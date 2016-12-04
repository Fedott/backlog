import * as React from "react";
import update from 'react/lib/update';

import StoryItem from "./StoryItem.jsx";
import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';

class StoriesList extends React.Component {
    static propTypes = {
        createForm: React.PropTypes.bool,
        projectId: React.PropTypes.string.isRequired,
        onStoryCreatedCallback: React.PropTypes.func,
    };

    constructor(props, context) {
        super(props, context);
        this.moveCard = this.moveCard.bind(this);

        this.state = {
            storiesCollection: [],
            createForm: props.createForm || false,
            projectId: props.projectId,
        };

        const request = {
            type: "get-stories",
            payload: {
                projectId: this.state.projectId,
            }
        };
        webSocketClient.sendRequest(request).then(function (response) {
            this.setState({storiesCollection: response.payload.stories});
        }.bind(this));
    }

    componentWillReceiveProps(nextProps) {
        if (undefined != nextProps.createForm) {
            this.setState({
                createForm: nextProps.createForm
            });
        }
    }

    moveCard(dragIndex, hoverIndex) {
        const stories = this.state.storiesCollection;
        const dragStory = stories[dragIndex];
        const hoverStory = stories[hoverIndex];

        webSocketClient.sendRequest({
            type: "move-story",
            "payload": {
                "projectId": this.props.projectId,
                "storyId": dragStory.id,
                "beforeStoryId": hoverStory.id,
            }
        });

        this.setState(update(this.state, {
            storiesCollection: {
                $splice: [
                    [dragIndex, 1],
                    [hoverIndex, 0, dragStory]
                ]
            }
        }));
    }

    onStoryCreated(story) {
        this.state.storiesCollection.unshift(story);
        this.props.onStoryCreatedCallback();
    }

    onStoryCompleted(story) {
        this.state.storiesCollection.unshift(story);
        this.forceUpdate();
    }

    render() {
        let createForm = null;
        if (this.state.createForm) {
            createForm = <StoryItem
                edit={true}
                isCreateForm={true}
                projectId={this.state.projectId}
                onStoryCreatedCallback={this.onStoryCreated.bind(this)}
                index={-1}
                onStoryCompleted={this.onStoryCompleted}
            />
            ;
        }

        const stories = this.state.storiesCollection.filter((story) => {
            return !story.isCompleted;
        }).map((story, i) => {
            return <StoryItem
                story={story}
                projectId={this.state.projectId}
                key={story.id}
                index={i}
                moveCard={this.moveCard}
                onStoryCompleted={this.onStoryCompleted.bind(this)}
            />
        });

        return (
            <div className="backlog-list mdl-grid">
                {createForm}
                {stories}
            </div>
        );
    }
}

export default StoriesList;

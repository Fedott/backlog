import * as React from "react";
import update from 'react/lib/update';

import StoryItem from "./StoryItem.jsx";
import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';

class StoriesList extends React.Component {
    constructor(props, context:any) {
        super(props, context);

        this.moveCard = this.moveCard.bind(this);

        this.state = {
            storiesCollection: [],
            createForm: props.createForm || false,
            filter: props.statusFilter || 'all'
        };

        webSocketClient.sendRequest({type: "get-stories"}).then(function (response) {
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

    render() {
        var createForm = null;
        if (this.state.createForm) {
            createForm = <StoryItem edit={true} isCreateForm={true} index={-1}/>;
        }

        var stories = this.state.storiesCollection.map((story, i) => {
            return <StoryItem
                story={story}
                key={story.id}
                index={i}
                moveCard={this.moveCard}
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

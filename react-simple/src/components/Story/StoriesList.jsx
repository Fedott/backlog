import * as React from "react";
import StoryItem from "./StoryItem.jsx";
import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';

class StoriesList extends React.Component {
    constructor(props, context:any) {
        super(props, context);

        this.state = {
            storiesCollection: [],
            createForm: props.createForm || false,
            filter: props.statusFilter || 'all'
        };

        webSocketClient.sendRequest({type: "get-stories"}).then(function (response) {
            this.setState({storiesCollection: response.payload.stories});
        }.bind(this));
    }

    toggleCreateForm() {
        this.setState({
            createForm: !this.state.createForm
        });

        if (this.props.onChangeCreateForm) {
            this.props.onChangeCreateForm(this.state.createForm);
        }
    }

    render() {

        var stories = this.state.storiesCollection.map((story) => {
            return <StoryItem story={story} key={story.id} />
        });

        return (
            <div className="backlog-list mdl-grid">
                <StoryItem edit={true} isCreateForm={true} />
                {stories}
            </div>
        );
    }
}

export default StoriesList;

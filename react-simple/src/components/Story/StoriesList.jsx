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
        console.log(props);


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

    render() {
        var createForm = null;
        if (this.state.createForm) {
            createForm = <StoryItem edit={true} isCreateForm={true} />;
        }

        var stories = this.state.storiesCollection.map((story) => {
            return <StoryItem story={story} key={story.id} />
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

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
            console.log('load stories', response);
            this.setState({storiesCollection: response.payload.stories});
        }.bind(this));
    }

    componentDidMount():void {
        // this.state.storiesCollection.setStatusFilter(this.state.filter);
        // this.state.storiesCollection.on('add remove change', this.forceUpdate.bind(this, null));
        // this.state.storiesCollection.fetch();
    }


    componentWillUpdate(nextProps, nextState, nextContext):void {
        // if (this.state.storiesCollection.statusFilter != nextProps.statusFilter) {
        //     this.state.storiesCollection.setStatusFilter(nextProps.statusFilter);
        //     this.state.storiesCollection.fetch();
        // }
    }

    componentWillUnmount():void {
        // this.state.storiesCollection.off(null, null, this);
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
            return <StoryItem story={story} key={story.number} />
        });

        return (
            <div className="backlog-list mdl-grid">
                {stories}
            </div>
        );
    }
}

export default StoriesList;

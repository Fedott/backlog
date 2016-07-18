import * as React from "react";
import * as ReactMDL from 'react-mdl';
import TextareaAutosize from 'react-autosize-textarea';
import webSocketClient from './../../libraries/WebSocket/WebSocketClient';

class StoryEditFrom extends React.Component {
    static propTypes = {
        story: React.PropTypes.object,
        isCreateForm: React.PropTypes.bool,
        onSaved: React.PropTypes.func,
        onCancel: React.PropTypes.func,
    };

    constructor(props, context:any) {
        super(props, context);

        var story = props.story || {title:"", text: "", id: null};
        this.state = {
            story: story,
            originalStory: story,
            status: 'editing',
            isCreateForm: props.isCreateForm || false,
        };

        this.onSaved = props.onSaved;
        this.onCancel = props.onCancel;
    }

    onChangeText(event) {
        this.state.story.text = event.target.value;
        this.forceUpdate();
    }

    onChangeTitle(event) {
        this.state.story.title = event.target.value;
        this.forceUpdate();
    }

    async onSave() {
        if (this.state.status == 'saving') {
            return;
        }

        this.setState({
            status: 'saving',
        });

        var response = await webSocketClient.sendRequest({
            type: this.state.isCreateForm ? "create-story" : "edit-story",
            payload: this.state.story,
        });

        if (response.type == 'story-created') {
            this.setState({
                status: 'editing',
                isCreateForm: false,
                story: response.payload,
                originalStory: response.payload,
            });
            this.onSaved(this.state.story);
        } else {
            this.onError();
        }
    }

    onError() {

    }

    render() {
        return (
            <ReactMDL.Card shadow={2} className="backlog-story mdl-cell mdl-cell--12-col">
                <ReactMDL.CardTitle expand className="backlog-story-title">
                    <input type="text" value={this.state.story.title} placeholder="Title" onChange={this.onChangeTitle.bind(this)}/>
                </ReactMDL.CardTitle>
                <ReactMDL.CardText>
                    <TextareaAutosize rows="3" value={this.state.story.text} placeholder="Text" onChange={this.onChangeText.bind(this)}/>
                </ReactMDL.CardText>

                <ReactMDL.CardActions border>
                    <ReactMDL.Button onClick={this.onSave.bind(this)}>
                        Сохранить
                    </ReactMDL.Button>
                    <ReactMDL.Button onClick={this.onCancel}>
                        Отмена
                    </ReactMDL.Button>
                </ReactMDL.CardActions>

                <ReactMDL.CardMenu>
                    <ReactMDL.IconButton name='check_box_outline_blank' />
                </ReactMDL.CardMenu>
            </ReactMDL.Card>
        );
    }
}

export default StoryEditFrom;

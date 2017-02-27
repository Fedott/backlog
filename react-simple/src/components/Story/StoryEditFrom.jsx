import * as React from "react";
import {
    Card,
    CardActions,
    CardText,
    CardTitle,
    FlatButton,
    Divider,
    TextField,
    LinearProgress
} from 'material-ui';

import webSocketClient from './../../libraries/WebSocket/WebSocketClient';

class StoryEditFrom extends React.Component {
    static propTypes = {
        story: React.PropTypes.object,
        projectId: React.PropTypes.string.isRequired,
        isCreateForm: React.PropTypes.bool,
        onSaved: React.PropTypes.func,
        onCancel: React.PropTypes.func,
    };

    constructor(props, context) {
        super(props, context);

        const story = props.story || {title: "", text: "", id: null};
        this.state = {
            story: story,
            projectId: props.projectId,
            originalStory: story,
            status: 'editing',
            isCreateForm: props.isCreateForm || false,
        };

        this.onSaved = props.onSaved;
        this.onCancel = props.onCancel;

        this.onChangeTitle = this.onChangeTitle.bind(this);
        this.onChangeText = this.onChangeText.bind(this);
        this.onSave = this.onSave.bind(this);
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
        if (this.state.status === 'saving') {
            return;
        }

        this.setState({
            status: 'saving',
        });

        const response = await webSocketClient.sendRequest({
            type: this.state.isCreateForm ? "create-story" : "edit-story",
            payload: this.state.isCreateForm ? {
                story: this.state.story,
                projectId: this.state.projectId,
            } : this.state.story,
        });

        if (response.type !== 'error') {
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
        let editIsLocked = false;
        let progressBar = null;

        if (this.state.status === 'saving') {
            progressBar = <LinearProgress mode="indeterminate" />;
            editIsLocked = true;
        }

        const titleField = <TextField
            id={"backlog-story-edit-title"}
            value={this.state.story.title}
            hintText={"Title"}
            onChange={this.onChangeTitle}
            disabled={editIsLocked}
            style={{
                "font-size": "24px",
                width: '100%',
            }}
        />;

        return (
            <Card className="backlog-story">
                <CardTitle title={titleField} className="backlog-story-title" />
                <CardText>
                    <TextField
                        id={"backlog-story-edit-text"}
                        multiLine={true}
                        value={this.state.story.text}
                        onChange={this.onChangeText}
                        hintText={"Text"}
                        disabled={editIsLocked}
                        style={{
                            "font-size": "14px",
                            "width": "100%",
                        }}
                    />
                </CardText>
                {progressBar}
                <Divider />
                <CardActions>
                    <FlatButton label="Сохранить" onTouchTap={this.onSave} disabled={editIsLocked} />
                    <FlatButton label="Отмена" onTouchTap={this.onCancel} disabled={editIsLocked} />
                </CardActions>
            </Card>
        );
    }
}

export default StoryEditFrom;

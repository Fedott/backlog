import * as React from "react";
import {
    Dialog,
    FlatButton,
    TextField
} from 'material-ui';

import webSocketClient from "../../../libraries/WebSocket/WebSocketClient.js";
import Request from "../../../libraries/WebSocket/Request";

export default class ShareDialog extends React.Component {
    static propTypes = {
        project: React.PropTypes.object.isRequired,
        onClose: React.PropTypes.func.isRequired,
    };


    constructor(props: P, context: any) {
        super(props, context);

        this.state = {
            username: '',
            usernameError: null,
        };

        this.onUsernameChange = this.onUsernameChange.bind(this);
        this.shareProject = this.shareProject.bind(this);
    }

    onUsernameChange(event) {
        this.setState({
            username: event.target.value
        });
    }

    async shareProject() {
        let request = new Request;
        request.type = 'project/share';
        request.payload = {
            userId: this.state.username,
            projectId: this.props.project.id,
        };

        let response = await webSocketClient.sendRequest(request);

        if (response.type === 'success') {
            this.props.onClose();
        } else {
            this.setState({
                usernameError: response.payload.message,
            });
        }
    }

    render() {
        const actions = [
            <FlatButton
                label="Войти"
                primary={true}
                onTouchTap={this.shareProject}
            />,
            <FlatButton
                label="Отмена"
                onTouchTap={this.props.onClose}
            />
        ];

        return (
            <Dialog
                open={true}
                className={"project-share-dialog"}
                actions={actions}
                title={"Пригласить пользователя"}
            >
                <TextField
                    value={this.state.username}
                    name={"username"}
                    onChange={this.onUsernameChange}
                    errorText={this.state.usernameError}
                />
            </Dialog>
        );
    }
}
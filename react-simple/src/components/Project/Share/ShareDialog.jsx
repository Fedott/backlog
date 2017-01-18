import * as React from "react";
import {Dialog, DialogTitle, DialogActions, DialogContent, Textfield, Button} from "react-mdl";

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
        return (
            <Dialog open={true}>
                <DialogTitle>
                    Пригласить пользователя
                </DialogTitle>
                <DialogContent>
                    <Textfield
                        value={this.state.username}
                        onChange={this.onUsernameChange}
                        label="Имя пользовтеля"
                        error={this.state.usernameError}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={this.props.onClose}>
                        Отмена
                    </Button>
                    <Button onClick={this.shareProject}>
                        Добавить
                    </Button>
                </DialogActions>
            </Dialog>
        )
    }
}
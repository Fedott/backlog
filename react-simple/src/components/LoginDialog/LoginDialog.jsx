import * as React from "react";
import {
    Layout,
    Header,
    Navigation,
    Content,
    FABButton,
    Button,
    Icon,
    Dialog,
    DialogContent,
    DialogTitle,
    DialogActions,
    Textfield,
} from 'react-mdl';
import webSocketClient from '../../libraries/WebSocket/WebSocketClient'
import User from "../Application/User";

export default class LoginDialog extends React.Component {
    static propTypes = {
        isOpen: React.PropTypes.bool.isRequired,
        onLoginSuccess: React.PropTypes.func.isRequired,
        onCancel: React.PropTypes.func.isRequired,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            isOpen: this.props.isOpen,
            isWaiting: false,
            loginFormFields: {
                username: null,
                password: null,
            },
        };

        this.onLoginSuccess = props.onLoginSuccess;
        this.onCancel = props.onCancel;
    }

    onUsernameChange(event) {
        this.state.loginFormFields.username = event.target.value;
    }

    onPasswordChange(event) {
        this.state.loginFormFields.password = event.target.value;
    }

    onKeyPress(event) {
        if (event.key === 'Enter') {
            this.onSignInClick();
        }
    }

    async onSignInClick() {
        this.setState({
            isWaiting: true,
        });

        let request = {
            type: 'login-username-password',
            payload: {
                username: this.state.loginFormFields.username,
                password: this.state.loginFormFields.password,
            }
        };

        let response = await webSocketClient.sendRequest(request);

        this.setState({
            isWaiting: false,
        });

        if (response.type == 'login-success') {
            let user = new User();
            user.username = response.payload.username;
            user.token = response.payload.token;
            this.onLoginSuccess(user);
        }
    }

    componentWillReceiveProps(nextProps) {
        if (this.state.isOpen != nextProps.isOpen) {
            this.setState({
                isOpen: nextProps.isOpen,
            });
        }
    }

    render() {
        return (
            <Dialog open={this.state.isOpen}>
                <DialogTitle>Login form</DialogTitle>
                <DialogContent>
                    <Textfield label="Username"
                               floatingLabel
                               onChange={this.onUsernameChange.bind(this)}
                               disabled={this.state.isWaiting}
                               onKeyPress={this.onKeyPress.bind(this)}
                    />
                    <Textfield label="Password"
                               floatingLabel
                               type="password"
                               onChange={this.onPasswordChange.bind(this)}
                               disabled={this.state.isWaiting}
                               onKeyPress={this.onKeyPress.bind(this)}
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={this.onCancel} disabled={this.state.isWaiting}>Cancel</Button>
                    <Button
                        colored
                        raised
                        onClick={this.onSignInClick.bind(this)}
                        disabled={this.state.isWaiting} >Sign in</Button>
                </DialogActions>
            </Dialog>
        );
    }
}
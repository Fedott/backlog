import * as React from "react";
import {
    Dialog,
    FlatButton,
    TextField,
    Snackbar
} from 'material-ui';
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
            isOpen: props.isOpen,
            isWaiting: false,
            loginFormFields: {
                username: null,
                password: null,
            },
            error: null,
        };

        this.onLoginSuccess = props.onLoginSuccess;
        this.onCancel = props.onCancel;

        this.onUsernameChange = this.onUsernameChange.bind(this);
        this.onKeyPress = this.onKeyPress.bind(this);
        this.onPasswordChange = this.onPasswordChange.bind(this);
        this.onSignInClick = this.onSignInClick.bind(this);
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
            error: null,
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

        if (response.type === 'login-success') {
            let user = new User();
            user.username = response.payload.username;
            user.token = response.payload.token;
            this.onLoginSuccess(user);
        } else {
            this.setState({error: response.payload.message});
        }
    }

    componentWillReceiveProps(nextProps) {
        if (this.state.isOpen !== nextProps.isOpen) {
            this.setState({
                isOpen: nextProps.isOpen,
            });
        }
    }

    render() {
        const actions = [
            <FlatButton
                label="Войти"
                primary={true}
                onTouchTap={this.onSignInClick}
                disabled={this.state.isWaiting}
            />,
            <FlatButton
                label="Отмена"
                onTouchTap={this.onCancel}
                disabled={this.state.isWaiting}
            />
        ];

        let snackbar = null;
        if (this.state.error) {
            snackbar = <Snackbar
                open={true}
                message={this.state.error}
                autoHideDuration={15000}
            />
        }

        return (
            <Dialog
                className="login-dialog"
                title="Авторизация"
                open={this.state.isOpen}
                actions={actions}
            >
                <TextField
                    id="login-dialog-username"
                    floatingLabelText="Имя пользователя"
                    onChange={this.onUsernameChange}
                    disabled={this.state.isWaiting}
                    onKeyPress={this.onKeyPress}
                />
                <br/>
                <TextField
                    id="login-dialog-password"
                    floatingLabelText="Пароль"
                    onChange={this.onPasswordChange}
                    disabled={this.state.isWaiting}
                    onKeyPress={this.onKeyPress}
                    type="password"
                />
                {snackbar}
            </Dialog>
        );
    }
}

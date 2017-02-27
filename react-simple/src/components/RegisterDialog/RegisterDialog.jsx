import * as React from "react";
import {
    Dialog,
    FlatButton,
    TextField
} from 'material-ui';
import webSocketClient from '../../libraries/WebSocket/WebSocketClient'
import User from "../Application/User";

export default class RegisterDialog extends React.Component {
    static propTypes = {
        isOpen: React.PropTypes.bool.isRequired,
        onRegisterSuccess: React.PropTypes.func.isRequired,
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
        };

        this.onRegisterSuccess = props.onRegisterSuccess;
        this.onCancel = props.onCancel;

        this.onUsernameChange = this.onUsernameChange.bind(this);
        this.onKeyPress = this.onKeyPress.bind(this);
        this.onPasswordChange = this.onPasswordChange.bind(this);
        this.onSignUpClick = this.onSignUpClick.bind(this);
    }

    onUsernameChange(event) {
        this.state.loginFormFields.username = event.target.value;
    }

    onPasswordChange(event) {
        this.state.loginFormFields.password = event.target.value;
    }

    onKeyPress(event) {
        if (event.key === 'Enter') {
            this.onSignUpClick();
        }
    }

    async onSignUpClick() {
        this.setState({
            isWaiting: true,
        });

        let request = {
            type: 'user-registration',
            payload: {
                username: this.state.loginFormFields.username,
                password: this.state.loginFormFields.password,
            }
        };

        let response = await webSocketClient.sendRequest(request);

        this.setState({
            isWaiting: false,
        });

        if (response.type === 'user-registered') {
            let user = new User();
            user.username = response.payload.username;
            user.token = response.payload.token;
            this.onRegisterSuccess(user);
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
                label="Зарегистрироваться"
                primary={true}
                onTouchTap={this.onSignUpClick}
                disabled={this.state.isWaiting}
            />,
            <FlatButton
                label="Отмена"
                onTouchTap={this.onCancel}
                disabled={this.state.isWaiting}
            />
        ];
        return (
            <Dialog
                className="register-dialog"
                title="Регистрация"
                open={this.state.isOpen}
                actions={actions}
            >
                <TextField
                    id="register-dialog-username"
                    floatingLabelText="Имя пользователя"
                    onChange={this.onUsernameChange}
                    disabled={this.state.isWaiting}
                    onKeyPress={this.onKeyPress}
                />
                <br/>
                <TextField
                    id="register-dialog-password"
                    floatingLabelText="Пароль"
                    onChange={this.onPasswordChange}
                    disabled={this.state.isWaiting}
                    onKeyPress={this.onKeyPress}
                    type="password"
                />
            </Dialog>
        );
    }
}
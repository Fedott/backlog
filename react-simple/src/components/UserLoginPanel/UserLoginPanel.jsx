import * as React from "react";
import {
    FlatButton,
} from 'material-ui';
import webSocketClient from '../../libraries/WebSocket/WebSocketClient'
import LoginDialog from "../LoginDialog/LoginDialog.jsx";
import RegisterDialog from "../RegisterDialog/RegisterDialog.jsx";

export default class UserLoginPanel extends React.Component {
    static propTypes = {
        onLogin: React.PropTypes.func.isRequired,
        onLogout: React.PropTypes.func.isRequired,
    };

    storageAuthTokenKey = 'auth-token';

    constructor(props, context) {
        super(props, context);

        this.state = {
            isLoginDialogOpen: false,
            isRegisterDialogOpen: false,
            isLogged: false,
            loggedUser: null,
        };

        this.onLoginExt = props.onLogin;
        this.onLogoutExt = props.onLogout;

        this.tryAutoLogin();

        this.toggleLoginDialog = this.toggleLoginDialog.bind(this);
        this.toggleRegisterDialog = this.toggleRegisterDialog.bind(this);
        this.onLogin = this.onLogin.bind(this);
        this.onLogout = this.onLogout.bind(this);
    }

    async tryAutoLogin() {
        let savedToken = window.localStorage.getItem(this.storageAuthTokenKey);

        if (savedToken) {
            let response = await webSocketClient.sendRequest({
                type: 'login-token',
                payload: {
                    token: savedToken,
                }
            });

            if (response.type == 'login-success') {
                this.onLogin(response.payload);
            }
        }
    }

    toggleLoginDialog() {
        this.setState({
            isLoginDialogOpen: !this.state.isLoginDialogOpen,
        });
    }

    toggleRegisterDialog() {
        this.setState({
            isRegisterDialogOpen: !this.state.isRegisterDialogOpen,
        });
    }

    onLogin(user) {
        window.localStorage.setItem(this.storageAuthTokenKey, user.token);

        this.setState({
            isLoginDialogOpen: false,
            isLogged: true,
            loggedUser: user,
        });

        this.onLoginExt(user);
    }

    onLogout() {
        window.localStorage.removeItem(this.storageAuthTokenKey);
        this.setState({
            isLoginDialogOpen: false,
            isLogged: false,
            loggedUser: null,
        });

        this.onLogoutExt();
    }

    render() {
        if (this.state.isLogged) {
            return (
                <div onClick={this.onLogout}>
                    Привет, {this.state.loggedUser.username}
                </div>
            );
        }

        return (
            <div>
                <LoginDialog isOpen={this.state.isLoginDialogOpen}
                             onCancel={this.toggleLoginDialog}
                             onLoginSuccess={this.onLogin}
                />
                <RegisterDialog isOpen={this.state.isRegisterDialogOpen}
                             onCancel={this.toggleRegisterDialog}
                             onRegisterSuccess={this.onLogin}
                />
                <FlatButton onTouchTap={this.toggleLoginDialog} id="login-button" label="Войти"/>
                <FlatButton onTouchTap={this.toggleRegisterDialog} id="register-button" label="Зарегистрироваться"/>
            </div>
        );
    }
}

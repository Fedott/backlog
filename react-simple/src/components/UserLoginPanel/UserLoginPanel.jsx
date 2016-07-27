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
import LoginDialog from "../LoginDialog/LoginDialog.jsx";

export default class UserLoginPanel extends React.Component {
    static propTypes = {
        onLogin: React.PropTypes.func.isRequired,
        onLogout: React.PropTypes.func.isRequired,
    };

    constructor(props, context:any) {
        super(props, context);

        this.state = {
            isLoginDialogOpen: false,
            isLogged: false,
            loggedUser: null,
        };

        this.onLoginExt = props.onLogin;
        this.onLogoutExt = props.onLogout;
    }

    toggleLoginDialog() {
        this.setState({
            isLoginDialogOpen: !this.state.isLoginDialogOpen,
        });
    }

    onLogin(user: User) {
        this.setState({
            isLoginDialogOpen: false,
            isLogged: true,
            loggedUser: user,
        });

        this.onLoginExt(user);
    }

    render() {
        if (this.state.isLogged) {
            return (
                <div>
                    Hello, {this.state.loggedUser.username}
                </div>
            );
        }

        return (
            <div>
                <LoginDialog isOpen={this.state.isLoginDialogOpen}
                             onCancel={this.toggleLoginDialog.bind(this)}
                             onLoginSuccess={this.onLogin.bind(this)}
                />
                <Button onClick={this.toggleLoginDialog.bind(this)}>Login</Button>
            </div>
        );
    }
}

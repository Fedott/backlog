import * as React from "react";
import ReactDOM from 'react-dom';
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
import HTML5Backend from 'react-dnd-html5-backend';
import { DragDropContext } from 'react-dnd';
import { Router, Route, IndexRoute, Link, hashHistory } from 'react-router'

import StoriesList from '../Story/StoriesList.jsx';
import UserLoginPanel from '../UserLoginPanel/UserLoginPanel.jsx';

// Import styles.
import User from "./User";

class Application extends React.Component {
    constructor(props:any, context:any) {
        super(props, context);

        this.state = {
            createForm: false,
            storyStatusFilter: "notCompleted",
            isLogged: false,
            loggedUser: null,
            isLoginDialogOpen: false,
            loginFormFields: {
                username: null,
                password: null,
            },
        };
    }

    toggleCreateForm() {
        console.log("change status create form");
        this.setState({
            createForm: !this.state.createForm
        });
    }

    changeFilter(event) {
        event.preventDefault();

        this.setState({storyStatusFilter: event.target.getAttribute('data')});
    }

    onUsernameChange(event) {
        this.state.loginFormFields.username = event.target.value;
    }

    onPasswordChange(event) {
        this.state.loginFormFields.password = event.target.value;
    }

    onLogin(user: User) {
        this.setState({
            isLogged: true,
            loggedUser: user,
        });
    }

    onLogout() {
        this.setState({
            isLogged: false,
            loggedUser: null,
        });
    }

    render() {

        return (
            <div>
                <Layout fixedHeader>
                    <Header title="Backlog">
                        <UserLoginPanel
                            onLogin={this.onLogin.bind(this)}
                            onLogout={this.onLogout.bind(this)}
                        />
                        <Navigation>
                            <Link to="/stories/all">All stories</Link>
                            <Link to="/stories/notCompleted">Not completed stories</Link>
                            <Link to="/stories/completed">Completed stories</Link>
                        </Navigation>
                    </Header>
                    <Content style={{width: "900px", margin: "0px auto", display: "block"}}>
                        {this.props.children}
                    </Content>
                </Layout>
            </div>
        )
    }
}

export default DragDropContext(HTML5Backend)(Application);

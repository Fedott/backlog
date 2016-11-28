import * as React from "react";
import {Layout, Header, Navigation, Content} from "react-mdl";
import HTML5Backend from "react-dnd-html5-backend";
import {DragDropContext} from "react-dnd";
import {Link, routerShape} from "react-router";
import UserLoginPanel from "../UserLoginPanel/UserLoginPanel.jsx";
import User from "./User";

class Application extends React.Component {
    constructor(props, context) {
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

    onLogin(user) {
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

    onHeaderClick() {
        this.context.router.push({ pathname: '/' });
    }

    render() {

        return (
            <div>
                <Layout fixedHeader>
                    <Header title={<span onClick={this.onHeaderClick.bind(this)} style={{cursor: 'pointer'}}>Backlog</span>}>
                        <UserLoginPanel
                            onLogin={this.onLogin.bind(this)}
                            onLogout={this.onLogout.bind(this)}
                        />
                        <Navigation>
                            <Link to="/projects">Projects</Link>
                        </Navigation>
                    </Header>
                    <Content style={{width: "900px", margin: "0px auto", display: "block"}}>
                        {this.props.children && React.cloneElement(this.props.children, {
                            isLogged: this.state.isLogged,
                        })}
                    </Content>
                </Layout>
            </div>
        )
    }
}

Application.contextTypes = {
    router: routerShape
};

export default DragDropContext(HTML5Backend)(Application);

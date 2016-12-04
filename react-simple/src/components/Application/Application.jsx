import * as React from "react";
import {Layout, Header, Navigation, Content} from "react-mdl";
import HTML5Backend from "react-dnd-html5-backend";
import {DragDropContext} from "react-dnd";
import {Link, routerShape} from "react-router";
import UserLoginPanel from "../UserLoginPanel/UserLoginPanel.jsx";

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

        this.onLogin = this.onLogin.bind(this);
        this.onLogout = this.onLogout.bind(this);
        this.onHeaderClick = this.onHeaderClick.bind(this);
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
        //noinspection JSUnresolvedVariable
        this.context.router.push({ pathname: '/' });
    }

    render() {

        return (
            <div>
                <Layout fixedHeader>
                    <Header title={<span onClick={this.onHeaderClick} style={{cursor: 'pointer'}}>Backlog</span>}>
                        <UserLoginPanel
                            onLogin={this.onLogin}
                            onLogout={this.onLogout}
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

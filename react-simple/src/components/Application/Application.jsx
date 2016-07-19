import * as React from "react";
import {Layout, Header, Navigation, Drawer, Content, FABButton, Icon} from 'react-mdl';
import StoriesList from '../Story/StoriesList.jsx';

// Import styles.
import '../../../node_modules/material-design-lite/material.js';

class Application extends React.Component {
    constructor(props:any, context:any) {
        super(props, context);

        this.state = {
            createForm: false,
            storyStatusFilter: "notCompleted"
        };
    }

    toggleCreateForm() {
        this.setState({createForm: !this.state.createForm});
    }

    changeFilter(event) {
        event.preventDefault();

        this.setState({storyStatusFilter: event.target.getAttribute('data')});
    }

    render() {
        return (
            <div>
                <Layout fixedHeader>
                    <Header title="Backlog">
                        <Navigation>
                            <a href="" onClick={this.changeFilter.bind(this)} data="all">All stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data="notCompleted">Not completed stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data="completed">Completed stories</a>
                        </Navigation>
                    </Header>
                    <Drawer>
                        <Navigation>
                            <a href="" onClick={this.changeFilter.bind(this)} data="all">All stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data="notCompleted">Not completed stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data="completed">Completed stories</a>
                        </Navigation>
                    </Drawer>
                    <Content style={{width: "900px", margin: "0px auto", display: "block"}}>
                        <StoriesList />
                        <FABButton id="add-story-button" colored ripple onClick={this.toggleCreateForm.bind(this)}>
                            <Icon name="add" />
                        </FABButton>
                    </Content>
                </Layout>
            </div>
        )
    }
}

export default Application;

import * as React from 'react';
import {Layout, Header, Navigation, Drawer, Content, FABButton, Icon} from 'react-mdl';
import {StoriesList} from './story/StoriesList';

export interface IApplicationProps {}
export interface IApplicationState {
    createForm?: boolean;
    storyStatusFilter?: StoryFilter;
}

export enum StoryFilter {
    All,
    Completed,
    NotCompleted,
}

export class Application extends React.Component<IApplicationProps, IApplicationState> {
    constructor(props:IApplicationProps, context:any) {
        super(props, context);

        this.state = {
            createForm: false,
            storyStatusFilter: StoryFilter.NotCompleted,
        };
    }

    toggleCreateForm() {
        this.setState({createForm: !this.state.createForm});
    }

    changeFilter(event) {
        event.preventDefault();

        this.setState({storyStatusFilter: event.target.getAttribute('data')});
    }

    render():JSX.Element {
        return (
            <div>
                <Layout fixedHeader>
                    <Header title="Backlog">
                        <Navigation>
                            <a href="" onClick={this.changeFilter.bind(this)} data={StoryFilter.All}>All stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data={StoryFilter.NotCompleted}>Not completed stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data={StoryFilter.Completed}>Completed stories</a>
                        </Navigation>
                    </Header>
                    <Drawer>
                        <Navigation>
                            <a href="" onClick={this.changeFilter.bind(this)} data={StoryFilter.All}>All stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data={StoryFilter.NotCompleted}>Not completed stories</a>
                            <a href="" onClick={this.changeFilter.bind(this)} data={StoryFilter.Completed}>Completed stories</a>
                        </Navigation>
                    </Drawer>
                    <Content>
                        <StoriesList
                            createForm={this.state.createForm}
                            onChangeCreateForm={this.toggleCreateForm.bind(this)}
                            statusFilter={this.state.storyStatusFilter}
                        />
                        <FABButton id="add-story-button" colored ripple onClick={this.toggleCreateForm.bind(this)}>
                            <Icon name="add" />
                        </FABButton>
                    </Content>
                </Layout>
            </div>
        );
    }
}

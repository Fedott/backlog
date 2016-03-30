import * as React from 'react';
import {Layout, Header, Navigation, Drawer, Content, FABButton, Icon} from 'react-mdl';
import {StoriesList} from './StoriesList';

export interface IApplicationProps {}
export interface IApplicationState {
    createForm?: boolean;
}

export class Application extends React.Component<IApplicationProps, IApplicationState> {
    constructor(props:IApplicationProps, context:any) {
        super(props, context);

        this.state = {createForm: false};
    }

    toggleCreateForm() {
        this.setState({createForm: !this.state.createForm});
    }

    render():JSX.Element {
        return (
            <div>
                <Layout fixedHeader>
                    <Header title="Backlog">
                        <Navigation>
                            <a href="">All stories</a>
                            <a href="">Not completed stories</a>
                            <a href="">Completed stories</a>
                        </Navigation>
                    </Header>
                    <Drawer>
                        <Navigation>
                            <a href="">All stories</a>
                            <a href="">Not completed stories</a>
                            <a href="">Completed stories</a>
                        </Navigation>
                    </Drawer>
                    <Content>
                        <StoriesList
                            createForm={this.state.createForm}
                            onChangeCreateForm={this.toggleCreateForm.bind(this)}
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

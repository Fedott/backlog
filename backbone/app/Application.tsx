import * as React from 'react';
import {Layout, Header, Navigation, Drawer, Content, FABButton, Icon} from 'react-mdl';
import {StoriesList} from './StoriesList';

export interface IApplicationProps {}
export interface IApplicationState {}

export class Application extends React.Component<IApplicationProps, IApplicationState> {
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
                        <StoriesList/>
                        <FABButton id="add-story-button" colored ripple>
                            <Icon name="add" />
                        </FABButton>
                    </Content>
                </Layout>
            </div>
        );
    }
}

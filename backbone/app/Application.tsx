import * as React from 'react';
import * as ReactMDL from 'react-mdl';
import {StoriesList} from './StoriesList';

export interface IApplicationProps {}
export interface IApplicationState {}

export class Application extends React.Component<IApplicationProps, IApplicationState> {

    render():JSX.Element {
        return (
            <ReactMDL.Layout fixedHeader>
                <ReactMDL.Header title="Backlog">
                    <ReactMDL.Navigation>
                        <a href="">All stories</a>
                        <a href="">Not completed stories</a>
                        <a href="">Completed stories</a>
                    </ReactMDL.Navigation>
                </ReactMDL.Header>
                <ReactMDL.Drawer>
                    <ReactMDL.Navigation>
                        <a href="">All stories</a>
                        <a href="">Not completed stories</a>
                        <a href="">Completed stories</a>
                    </ReactMDL.Navigation>
                </ReactMDL.Drawer>
                <ReactMDL.Content>
                    <StoriesList/>
                    <ReactMDL.FABButton id="add-story-button" colored ripple>
                        <ReactMDL.Icon name="add" />
                    </ReactMDL.FABButton>
                </ReactMDL.Content>
            </ReactMDL.Layout>
        );
    }
}

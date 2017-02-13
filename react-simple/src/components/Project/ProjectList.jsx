import * as React from "react";

import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';
import ProjectListItem from './ProjectListItem.jsx';

export default class ProjectList extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            createForm: false,
            projectCollection: [],
        };

        webSocketClient.sendRequest({
            type: 'get-projects',
        }).then(function (response) {
            this.setState({projectCollection: response.payload.projects})
        }.bind(this));
    }

    componentWillReceiveProps(nextProps) {
        if (undefined !== nextProps.createForm) {
            this.setState({
                createForm: nextProps.createForm
            });
        }
    }

    render() {
        let createForm = null;
        if (this.state.createForm) {
            createForm = <ProjectListItem edit={true} isCreateForm={true} index={-1}/>;
        }

        let projectItems = this.state.projectCollection.map((project, i) => {
            return <ProjectListItem project={project} key={i} />
        });

        return (
            <div className="backlog-list">
                {createForm}
                {projectItems}
            </div>
        )
    }
}

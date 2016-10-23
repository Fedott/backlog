import * as React from "react";
import * as ReactMDL from 'react-mdl';
import {browserHistory} from "react-router";
import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';

class ProjectView extends React.Component {
    static propTypes = {
        project: React.PropTypes.object,
        onChangeEdit: React.PropTypes.func,
        onDeleted: React.PropTypes.func,
    };

    constructor(props, context:any) {
        super(props, context);

        this.state = {
            project: props.project,
        };

        this.onChangeEdit = props.onChangeEdit || (() => {});
        this.onDeleted = props.onDeleted || (() => {});
    }

    async onDelete() {
        var response = await webSocketClient.sendRequest({
            type: "delete-project",
            payload: {projectId: this.state.project.id},
        });

        this.onDeleted();
    }

    goToStoryList() {
        browserHistory.push(`/project/${this.state.project.id}/stories`);
    }

    render() {
        return (
            <ReactMDL.Card shadow={2} className="backlog-project">
                <ReactMDL.CardTitle expand className="backlog-project-name">
                    {this.state.project.name}
                </ReactMDL.CardTitle>

                <ReactMDL.CardActions border>
                    <ReactMDL.Button onClick={this.onChangeEdit}>
                        Редактировать
                    </ReactMDL.Button>
                    <ReactMDL.Button onClick={this.goToStoryList.bind(this)}>
                        Список историй
                    </ReactMDL.Button>
                </ReactMDL.CardActions>

                <ReactMDL.CardMenu>
                    <ReactMDL.IconButton name='more_vert' id={"card-project-menu" + this.state.project.id} />
                    <ReactMDL.Menu
                        target={"card-project-menu" + this.state.project.id}
                        align="right"
                        ripple
                    >
                        <ReactMDL.MenuItem
                            onClick={this.onDelete.bind(this)}
                        >Delete</ReactMDL.MenuItem>
                    </ReactMDL.Menu>
                </ReactMDL.CardMenu>
            </ReactMDL.Card>
        );
    }
}

export default ProjectView;

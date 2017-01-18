import * as React from "react";
import * as ReactMDL from "react-mdl";
import {browserHistory} from "react-router";
import ShareDialog from "./Share/ShareDialog.jsx";
import webSocketClient from "../../libraries/WebSocket/WebSocketClient.js";

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
            shareDialogOpen: false,
        };

        this.onChangeEdit = props.onChangeEdit || (() => {});
        this.onDeleted = props.onDeleted || (() => {});

        this.onDelete = this.onDelete.bind(this);
        this.goToStoryList = this.goToStoryList.bind(this);
        this.toggleShareDialog = this.toggleShareDialog.bind(this);
    }

    async onDelete() {
        let response = await webSocketClient.sendRequest({
            type: "delete-project",
            payload: {projectId: this.state.project.id},
        });

        if (response.type === 'project-deleted') {
            this.onDeleted();
        }
    }

    toggleShareDialog() {
        this.setState({
            shareDialogOpen: !this.state.shareDialogOpen
        });
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
                    <ReactMDL.Button onClick={this.goToStoryList}>
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
                            onClick={this.toggleShareDialog}
                        >
                            Пригласить пользователя
                        </ReactMDL.MenuItem>
                        <ReactMDL.MenuItem
                            onClick={this.onDelete}
                        >
                            Удалить
                        </ReactMDL.MenuItem>
                    </ReactMDL.Menu>
                </ReactMDL.CardMenu>
                {
                    this.state.shareDialogOpen &&
                    <ShareDialog
                        onClose={this.toggleShareDialog}
                        project={this.state.project}
                    />
                }
            </ReactMDL.Card>
        );
    }
}

export default ProjectView;

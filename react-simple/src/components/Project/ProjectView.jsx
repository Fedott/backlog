import * as React from "react";
import {
    Card,
    CardActions,
    CardTitle,
    FlatButton,
    Divider
} from 'material-ui';
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
        const style = {
            width: '100%',
            margin: '10px',
        };

        return (
            <Card className="backlog-project" style={style} data-project-id={this.state.project.id}>
                <CardTitle title={this.state.project.name} className="backlog-project-name"/>
                <Divider />
                <CardActions showExpandableButton={true}>
                    <FlatButton label={"Список историй"} onTouchTap={this.goToStoryList} />
                    <FlatButton label={"Редактировать"} onTouchTap={this.onChangeEdit} />
                </CardActions>
                <CardActions expandable={true}>
                    <FlatButton label={"Пригласить пользователей"} onTouchTap={this.toggleShareDialog} />
                    <FlatButton label={"Удалить"} onTouchTap={this.onDelete} />
                </CardActions>
                {
                    this.state.shareDialogOpen &&
                    <ShareDialog
                        onClose={this.toggleShareDialog}
                        project={this.state.project}
                    />
                }
            </Card>
        );
    }
}

export default ProjectView;

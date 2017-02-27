import * as React from "react";
import {
    Card,
    CardActions,
    CardTitle,
    FlatButton,
    Divider,
    TextField,
    LinearProgress
} from 'material-ui';
import webSocketClient from './../../libraries/WebSocket/WebSocketClient';

class ProjectEditFrom extends React.Component {
    static propTypes = {
        project: React.PropTypes.object,
        isCreateForm: React.PropTypes.bool,
        onSaved: React.PropTypes.func,
        onCancel: React.PropTypes.func,
    };

    constructor(props, context:any) {
        super(props, context);

        const project = props.project || {name: "", id: null};
        this.state = {
            project: project,
            originalProject: project,
            status: 'editing',
            isCreateForm: props.isCreateForm || false,
        };

        this.onSaved = props.onSaved;
        this.onCancel = props.onCancel;

        this.onChangeName = this.onChangeName.bind(this);
        this.onSave = this.onSave.bind(this);
    }

    onChangeName(event) {
        this.state.project.name = event.target.value;
        this.forceUpdate();
    }

    async onSave() {
        if (this.state.status === 'saving') {
            return;
        }

        this.setState({
            status: 'saving',
        });

        const response = await webSocketClient.sendRequest({
            type: this.state.isCreateForm ? "create-project" : "edit-project",
            payload: this.state.project,
        });

        if (response.type !== 'error') {
            this.setState({
                status: 'editing',
                isCreateForm: false,
                project: response.payload,
                originalProject: response.payload,
            });
            this.onSaved(this.state.project);
        } else {
            this.onError();
        }
    }

    onError() {

    }

    render() {
        let editIsLocked = false;
        let progressBar = null;

        if (this.state.status === 'saving') {
            progressBar = <LinearProgress mode="indeterminate" />;
            editIsLocked = true;
        }

        const style = {
            width: '100%',
            margin: '10px',
        };

        const titleField = <TextField
            id={"backlog-project-edit-name"}
            value={this.state.project.name}
            hintText={"Name"}
            name={"name"}
            onChange={this.onChangeName}
            disabled={editIsLocked}
            style={{
                "font-size": "24px",
                width: '100%',
            }}
        />;

        return (
            <Card className="backlog-project" style={style}>
                <CardTitle title={titleField} className="backlog-project-name" />
                {progressBar}
                <Divider />
                <CardActions>
                    <FlatButton label="Сохранить" onTouchTap={this.onSave} disabled={editIsLocked} />
                    <FlatButton label="Отмена" onTouchTap={this.onCancel} disabled={editIsLocked} />
                </CardActions>
            </Card>
        );
    }
}

export default ProjectEditFrom;

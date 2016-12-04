import * as React from "react";
import * as ReactMDL from 'react-mdl';
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
        if (this.state.status == 'saving') {
            return;
        }

        this.setState({
            status: 'saving',
        });

        const response = await webSocketClient.sendRequest({
            type: this.state.isCreateForm ? "create-project" : "edit-project",
            payload: this.state.project,
        });

        if (response.type != 'error') {
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

        if (this.state.status == 'saving') {
            progressBar = <ReactMDL.ProgressBar indeterminate style={{width: "100%"}}/>;
            editIsLocked = true;
        }

        return (
            <ReactMDL.Card shadow={2} className="backlog-project mdl-cell mdl-cell--12-col">
                <ReactMDL.CardTitle expand className="backlog-project-name">
                    <input
                        type="text"
                        value={this.state.project.name}
                        placeholder="Name"
                        onChange={this.onChangeName}
                        disabled={editIsLocked}
                    />
                </ReactMDL.CardTitle>
                {progressBar}

                <ReactMDL.CardActions border>
                    <ReactMDL.Button
                        onClick={this.onSave}
                        disabled={editIsLocked}
                    >
                        Сохранить
                    </ReactMDL.Button>
                    <ReactMDL.Button
                        onClick={this.onCancel}
                        disabled={editIsLocked}
                    >
                        Отмена
                    </ReactMDL.Button>
                </ReactMDL.CardActions>
            </ReactMDL.Card>
        );
    }
}

export default ProjectEditFrom;

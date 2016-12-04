import * as React from "react";

import ProjectView from "./ProjectView.jsx";
import ProjectEditForm from "./ProjectEditFrom.jsx";

export default class ProjectListItem extends React.Component {
    static propTypes = {
        project: React.PropTypes.object,
        edit: React.PropTypes.bool,
        isCreateForm: React.PropTypes.bool,
    };

    constructor(props, context:any) {
        super(props, context);

        this.state = {
            project: props.project,
            edit: props.edit || false,
            isCreateForm: props.isCreateForm || false,
            isDeleted: false,
        };
    }

    onChangeEdit() {
        this.setState({
            edit: !this.state.edit,
        });
    }

    onSaved(project) {
        this.setState({
            project: project,
            edit: false,
            isCreateForm: false,
        });
    }

    onDeleted() {
        this.setState({
            isDeleted: true,
        });
    }

    render() {
        if (this.state.isDeleted) {
            return null;
        }

        if (this.state.edit) {
            return <ProjectEditForm
                project={this.state.project}
                onCancel={this.onChangeEdit.bind(this)}
                onSaved={this.onSaved.bind(this)}
                isCreateForm={this.state.isCreateForm}
            />;
        } else {
            return (
                <div className="mdl-cell mdl-cell--12-col">
                    <ProjectView
                        project={this.state.project}
                        onChangeEdit={this.onChangeEdit.bind(this)}
                        onDeleted={this.onDeleted.bind(this)}
                    />
                </div>
            );
        }
    }
}

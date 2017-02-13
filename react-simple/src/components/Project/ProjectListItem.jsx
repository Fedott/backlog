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

        this.onChangeEdit = this.onChangeEdit.bind(this);
        this.onSaved = this.onSaved.bind(this);
        this.onDeleted = this.onDeleted.bind(this);
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

        let projectContent;

        if (this.state.edit) {
            projectContent = <ProjectEditForm
                project={this.state.project}
                onCancel={this.onChangeEdit}
                onSaved={this.onSaved}
                isCreateForm={this.state.isCreateForm}
            />
        } else {
            projectContent = <ProjectView
                project={this.state.project}
                onChangeEdit={this.onChangeEdit}
                onDeleted={this.onDeleted}
            />
        }

        return projectContent;
    }
}

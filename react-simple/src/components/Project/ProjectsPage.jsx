import * as React from "react";
import {FloatingActionButton} from 'material-ui';
import ContentAdd from 'material-ui/svg-icons/content/add';
import ProjectList from './ProjectList.jsx';


class ProjectsPage extends React.Component {
    static propTypes = {
        isLogged: React.PropTypes.bool,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            createForm: false,
            isLogged: props.isLogged,
        };

        this.toggleCreateForm = this.toggleCreateForm.bind(this);
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            isLogged: nextProps.isLogged,
        })
    }

    toggleCreateForm() {
        this.setState({
            createForm: !this.state.createForm
        });
    }

    render() {
        if (!this.state.isLogged) {
            return <div></div>;
        }
        return (<div>
            <ProjectList createForm={this.state.createForm} />
            <FloatingActionButton
                className="add-project-button"
                onTouchTab={this.toggleCreateForm}
                onClick={this.toggleCreateForm}
                secondary={true}
                style={{
                    position: 'fixed',
                    bottom: '30px',
                    right: '30px',
                }}
            >
                <ContentAdd />
            </FloatingActionButton>
        </div>);
    }
}

export default ProjectsPage

import * as React from "react";
import {
    FABButton,
    Icon,
} from 'react-mdl';
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
        }
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
            <FABButton id="add-story-button" colored ripple onClick={this.toggleCreateForm.bind(this)}>
                <Icon name="add" />
            </FABButton>
        </div>);
    }
}

export default ProjectsPage

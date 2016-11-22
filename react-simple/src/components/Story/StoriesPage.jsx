import * as React from "react";
import {
    FABButton,
    Icon,
} from 'react-mdl';

import StoriesList from '../Story/StoriesList.jsx';

class StoriesPage extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            createForm: false,
            isLogged: props.isLogged,
            projectId: props.params.projectId,
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            isLogged: nextProps.isLogged,
        })
    }

    toggleCreateForm() {
        this.setState({
            createForm: !this.state.createForm,
        });
    }

    render() {
        if (!this.state.isLogged) {
            return <div></div>;
        }

        return (<div>
            <StoriesList
                createForm={this.state.createForm}
                projectId={this.state.projectId}
                onStoryCreatedCallback={this.toggleCreateForm.bind(this)}
            />
            <FABButton id="add-story-button" colored ripple onClick={this.toggleCreateForm.bind(this)}>
                <Icon name="add" />
            </FABButton>
        </div>);
    }
}

export default StoriesPage

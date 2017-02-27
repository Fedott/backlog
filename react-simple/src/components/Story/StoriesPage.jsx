import * as React from "react";
import {FloatingActionButton} from 'material-ui';
import ContentAdd from 'material-ui/svg-icons/content/add';

import StoriesList from '../Story/StoriesList.jsx';

class StoriesPage extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            createForm: false,
            isLogged: props.isLogged,
            projectId: props.params.projectId,
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
                onStoryCreatedCallback={this.toggleCreateForm}
            />
            <FloatingActionButton
                className="add-story-button"
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

export default StoriesPage

import * as React from "react";
import {
    FABButton,
    Icon,
} from 'react-mdl';

import StoriesList from '../Story/StoriesList.jsx';

class StoriesPage extends React.Component {
    constructor(props, context:any) {
        super(props, context);

        this.state = {
            createForm: false,
        }
    }

    toggleCreateForm() {
        console.log("change status create form");
        this.setState({
            createForm: !this.state.createForm
        });
    }

    render() {
        return (<div>
            <StoriesList createForm={this.state.createForm}/>
            <FABButton id="add-story-button" colored ripple onClick={this.toggleCreateForm.bind(this)}>
                <Icon name="add" />
            </FABButton>
        </div>);
    }
}

export default StoriesPage
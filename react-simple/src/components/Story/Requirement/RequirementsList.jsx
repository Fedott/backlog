import * as React from "react";
import {List, Subheader} from "material-ui";
import webSocketClient from "../../../libraries/WebSocket/WebSocketClient.js";
import Request from "../../../libraries/WebSocket/Request";
import Response from "../../../libraries/WebSocket/Response";
import Requirement from "./Requirement";
import RequirementListItem from "./RequirementListItem.jsx";

export default class RequirementsList extends React.Component {
    static propTypes = {
        storyId: React.PropTypes.string.isRequired,
        editMode: React.PropTypes.bool
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            requirementsCollection: [],
            storyId: props.storyId,
            editMode: props.editMode || false,
        };

        const request = new Request('story/requirements/getAll', {
            storyId: this.state.storyId,
        });

        webSocketClient.sendRequest(request).then(function (response: Response) {
            let requirements = response.payload.requirements.map((requirement: Requirement) => {
                requirement.storyId = this.state.storyId;
                return requirement;
            });
            this.setState({
                requirementsCollection: requirements,
            });
        }.bind(this));
    }

    onCreate = (requirement: Requirement) => {
        this.state.requirementsCollection.push(requirement);
        this.forceUpdate();
    };

    render() {
        let requirements = this.state.requirementsCollection.map(function (requirement: Requirement) {
            return <RequirementListItem requirement={requirement} />
        });

        let createForm = null;

        if (this.state.editMode) {
            createForm = <RequirementListItem requirement={{storyId: this.state.storyId}} createForm={true} editMode={true} onSavedHandler={this.onCreate}/>
        }

        return (
            <List>
                <Subheader>Требования</Subheader>
                {requirements}
                {createForm}
            </List>
        );
    }
}

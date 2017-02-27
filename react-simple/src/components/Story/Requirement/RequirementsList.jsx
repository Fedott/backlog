import * as React from "react";
import {List} from "material-ui";
import webSocketClient from "../../../libraries/WebSocket/WebSocketClient.js";
import Request from "../../../libraries/WebSocket/Request";
import Response from "../../../libraries/WebSocket/Response";
import Requirement from "./Requirement";
import RequirementListItem from "./RequirementListItem.jsx";

export default class RequirementsList extends React.Component {
    static propTypes = {
        storyId: React.PropTypes.string.isRequired,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            requirementsCollection: [],
            storyId: props.storyId,
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
            })
        }.bind(this));
    }

    render() {
        let requirements = this.state.requirementsCollection.reverse().map(function (requirement: Requirement) {
            return <RequirementListItem requirement={requirement} />
        });

        return (
            <List>
                {requirements}
                {<RequirementListItem requirement={{storyId: this.state.storyId}} createForm={true} editMode={true} />}
            </List>
        );
    }
}

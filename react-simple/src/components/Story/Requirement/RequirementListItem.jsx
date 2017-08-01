import * as React from "react";
import {Checkbox, IconButton, ListItem, TextField} from "material-ui";
import EditorModeEdit from "material-ui/svg-icons/editor/mode-edit";
import ContentSave from "material-ui/svg-icons/content/save";
import webSocketClient from '../../../libraries/WebSocket/WebSocketClient.js';
import Request from "../../../libraries/WebSocket/Request";

export default class RequirementListItem extends React.Component {
    static propsType = {
        requirement: React.PropTypes.object.isRequired,
        editMode: React.PropTypes.bool,
        createForm: React.PropTypes.bool,

        onSavedHandler: React.PropTypes.func,
        onCanceledHandler: React.PropTypes.func,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            requirement: props.requirement,
            editMode: props.editMode || false,
            createForm: props.createForm || false,
            requirementText: null,
            disabled: false,
        };
    }

    componentWillReceiveProps = (nextProps) => {
        this.setState({
            requirement: nextProps.requirement,
            editMode: nextProps.editMode || false,
            createForm: nextProps.createForm || false,
            requirementText: null,
            disabled: false,
        });
    };

    enableEditMode = () => {
        this.setState({
            editMode: true,
            requirementText: this.state.requirement.text,
        });
    };

    cancel = () => {
        this.setState({
            editMode: false,
        });

        if (this.props.onCanceledHandler) {
            this.props.onCanceledHandler(this.state.requirement);
        }
    };

    save = async () => {
        this.setState({
            disabled: true,
        });

        let request;

        if (this.state.createForm) {
            request = {
                type: 'story/requirements/create',
                payload: {
                    storyId: this.state.requirement.storyId,
                    text: this.state.requirementText,
                }
            };
        } else {
            request = {
                type: 'story/requirements/save',
                payload: {
                    id: this.state.requirement.id,
                    text: this.state.requirementText,
                }
            };
        }

        let response = await webSocketClient.sendRequest(request);

        if (response.type === 'requirement-saved' || (this.state.createForm && response.type === 'requirement-created')) {
            if (this.state.createForm) {
                this.state.requirement.id = response.payload.id;
            }
            this.state.requirement.text = this.state.requirementText;
            this.state.requirement.completed = payload.completed;

            this.setState({
                disabled: false,
                editMode: false,
                createForm: false,
            });

            if (this.props.onSavedHandler) {
                this.props.onSavedHandler(this.state.requirement);
            }
        } else {
            this.setState({
                disabled: false,
            });
        }
    };

    handleTextChange = (event) => {
        this.setState({
            requirementText: event.target.value,
        });
    };

    handleKeyPress = (event: Event) => {
        if (event.keyCode === 27) {
            this.cancel();
        }

        if (event.keyCode === 13) {
            this.save();
        }
    };

    handleCheckboxClick = async () => {
        const request = new Request('story/requirements/change-completed', {
            requirementId: this.state.requirement.id,
            completed: !this.state.requirement.completed,
        });

        const response = await webSocketClient.sendRequest(request);

        if (response.type === 'success') {
            this.state.requirement.completed = !this.state.requirement.completed;
        }

        this.forceUpdate();
    };

    render() {
        let rightControls;
        let leftControls;
        let primaryText;

        if (this.state.editMode) {
            primaryText = <TextField value={this.state.requirementText}
                data-requirement-form-text-field
                onChange={this.handleTextChange}
                onKeyUp={this.handleKeyPress}
                fullWidth={true}
                disabled={this.state.disabled}
            />;
            rightControls = <IconButton
                data-requirement-form-save
                onTouchTap={this.save}
                disabled={this.state.disabled}
            ><ContentSave /></IconButton>;
            leftControls = <Checkbox checked={this.state.requirement.completed} disabled={true}/>;
        } else {
            primaryText = this.state.requirement.text;
            leftControls = <Checkbox checked={this.state.requirement.completed} onCheck={this.handleCheckboxClick} />;
            rightControls = <IconButton
                onTouchTap={this.enableEditMode}
                disabled={this.state.disabled}
            >
                <EditorModeEdit />
            </IconButton>;
        }

        return <ListItem
            primaryText={primaryText}
            leftCheckbox={leftControls}
            rightIconButton={rightControls}
        />
    }
}

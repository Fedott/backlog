import * as React from "react";
import {Checkbox, IconButton, ListItem, TextField} from "material-ui";
import EditorModeEdit from "material-ui/svg-icons/editor/mode-edit";
import ContentSave from "material-ui/svg-icons/content/save";
import webSocketClient from '../../../libraries/WebSocket/WebSocketClient.js';

export default class RequirementListItem extends React.Component {
    static propsType = {
        requirement: React.PropTypes.object.isRequired,
        editMode: React.PropTypes.bool,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            requirement: props.requirement,
            editMode: props.editMode || false,
            requirementText: null,
            disabled: false,
        };
    }

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
    };

    save = async () => {
        this.setState({
            disabled: true,
        });
        let response = await webSocketClient.sendRequest({
            type: 'story/requirements/save',
            payload: {
                id: this.state.requirement.id,
                text: this.state.requirementText,
            }
        });

        if (response.type === 'success') {
            this.state.requirement.text = this.state.requirementText;

            this.setState({
                disabled: false,
                editMode: false,
            })
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
    };

    render() {
        let rightControls;
        let primaryText;

        if (this.state.editMode) {
            primaryText = <TextField value={this.state.requirementText}
                onChange={this.handleTextChange}
                onKeyUp={this.handleKeyPress}
                fullWidth={true}
                disabled={this.state.disabled}
                autoFocus
            />;
            rightControls = <IconButton onTouchTap={this.save} disabled={this.state.disabled}><ContentSave /></IconButton>
        } else {
            primaryText = this.state.requirement.text;
            rightControls = <IconButton
                onTouchTap={this.enableEditMode}
                disabled={this.state.disabled}
            >
                <EditorModeEdit />
            </IconButton>;
        }

        return <ListItem
            primaryText={primaryText}
            leftCheckbox={<Checkbox checked={this.state.requirement.completed} />}
            rightIconButton={rightControls}
        />
    }
}
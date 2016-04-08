import * as React from 'react';
import * as ReactMDL from 'react-mdl'
import {RequirementModel} from "../../RequirementModel";

export interface IRequirementItemState {
    isEditMode?: boolean;
}
export interface IRequirementItemProps {
    requirementModel: RequirementModel;
    isEditMode?: boolean;
}

export class RequirementItem extends React.Component<IRequirementItemProps, IRequirementItemState> {
    protected tempNameValue: string;

    constructor(props:IRequirementItemProps, context:any) {
        super(props, context);

        this.state = {
            isEditMode: props.isEditMode || false,
        }
    }

    toggleEditMode() {
        this.tempNameValue = undefined;
        this.setState({
            isEditMode: !this.state.isEditMode,
        });
    }

    changeHandler(event) {
        this.tempNameValue = event.target.value;
    }

    save() {
        if (this.tempNameValue) {
            this.props.requirementModel.set('name', this.tempNameValue);
            this.props.requirementModel.save();
        }

        this.toggleEditMode();
    }

    cancel() {
        this.toggleEditMode();
    }

    render():JSX.Element {
        var content;
        var actions;
        if (this.state.isEditMode) {
            actions = (
                <div>
                    <ReactMDL.IconButton
                        name="save"
                        onClick={this.save.bind(this)}
                    />
                    <ReactMDL.IconButton
                        name="cancel"
                        onClick={this.cancel.bind(this)}
                    />
                </div>
            );
            content = <ReactMDL.Textfield
                label="Name"
                onChange={this.changeHandler.bind(this)}
                defaultValue={this.props.requirementModel.get('name')}
            />;
        } else {
            actions = <ReactMDL.IconButton
                name="edit"
                onClick={this.toggleEditMode.bind(this)}
            />;
            content = this.props.requirementModel.get('name');
        }

        return (
            <ReactMDL.ListItem>
                <ReactMDL.ListItemContent>
                    {content}
                </ReactMDL.ListItemContent>
                <ReactMDL.ListItemAction>
                    {actions}
                </ReactMDL.ListItemAction>
            </ReactMDL.ListItem>
        );
    }
}

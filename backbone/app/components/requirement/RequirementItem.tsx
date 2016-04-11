import * as React from 'react';
import * as ReactMDL from 'react-mdl'
import {RequirementModel} from "../../RequirementModel";

export interface IRequirementItemState {
    requirementModel?: RequirementModel;
    isEditMode?: boolean;
    isCreateForm?: boolean;
}
export interface IRequirementItemProps {
    requirementModel: RequirementModel;
    isEditMode?: boolean;
    isCreateForm?: boolean;
}

export class RequirementItem extends React.Component<IRequirementItemProps, IRequirementItemState> {
    protected tempNameValue: string;
    protected nameInput;

    constructor(props:IRequirementItemProps, context:any) {
        super(props, context);

        this.state = {
            requirementModel: props.requirementModel,
            isEditMode: props.isEditMode || false,
            isCreateForm: props.isCreateForm || false,
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
            if (!this.state.isCreateForm) {
                this.state.requirementModel.set('name', this.tempNameValue);
                this.state.requirementModel.save();
            }

            if (this.state.isCreateForm) {
                this.state.requirementModel.collection.create({
                    'name': this.tempNameValue,
                });
                this.initCreateForm();
            }
        }

        if (!this.state.isCreateForm) {
            this.toggleEditMode();
        }
    }

    private initCreateForm() {
        if (this.state.isCreateForm) {
            this.tempNameValue = '';
            console.log(this.nameInput.refs.input.value);
            this.nameInput.refs.input.value = '';
            console.log(this.nameInput.refs.input.value);

            this.forceUpdate();
        }
    }

    cancel() {
        if (!this.state.isCreateForm) {
            this.toggleEditMode();
        } else {
            this.initCreateForm();
        }
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
                defaultValue={this.state.requirementModel.get('name')}
                ref={(ref) => {this.nameInput = ref;}}
            />;
        } else {
            actions = <ReactMDL.IconButton
                name="edit"
                onClick={this.toggleEditMode.bind(this)}
            />;
            content = this.state.requirementModel.get('name');
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

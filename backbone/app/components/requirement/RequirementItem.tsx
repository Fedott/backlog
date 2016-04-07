import * as React from 'react';
import * as ReactMDL from 'react-mdl'
import {RequirementModel} from "../../RequirementModel";

export interface IRequirementItemState {}
export interface IRequirementItemProps {
    requirementModel: RequirementModel;
}

export class RequirementItem extends React.Component<IRequirementItemProps, IRequirementItemState> {
    render():JSX.Element {
        return (
            <ReactMDL.ListItem>
                {this.props.requirementModel.get('name')}
            </ReactMDL.ListItem>
        );
    }
}

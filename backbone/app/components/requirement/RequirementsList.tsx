import * as React from 'react';
import * as ReactMDL from 'react-mdl';
import {RequirementsCollection} from "../../RequirementsCollection";
import {RequirementModel} from "../../RequirementModel";
import {RequirementItem} from "./RequirementItem";

export interface IRequirementsListState {}
export interface IRequirementsListProps {
    requirementsCollection: RequirementsCollection;
}

export class RequirementsList extends React.Component<IRequirementsListProps, IRequirementsListState> {
    componentDidMount():void {
        this.props.requirementsCollection.on('add remove change', this.forceUpdate.bind(this, null));
    }

    componentWillUnmount():void {
        this.props.requirementsCollection.off(null, null, this);
    }

    render():JSX.Element {
        var items = this.props.requirementsCollection.map((requirement:RequirementModel) => {
            return <RequirementItem requirementModel={requirement} key={requirement.id} />
        });
        
        return (
            <ReactMDL.List className="requirements-list">
                {items}
            </ReactMDL.List>
        );
    }
}

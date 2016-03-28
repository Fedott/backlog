import {StoryModel} from "./StoryModel";
import * as React from 'react';
import * as ReactMDL from 'react-mdl'

export interface IStoryItemProps {
    storyModel: StoryModel;
}

export interface IStoryItemState {
    isEdit?: boolean;
    isRequirements?: boolean;
}

export class StoryItem extends React.Component<IStoryItemProps, IStoryItemState> {
    constructor(props: IStoryItemProps, context:any) {
        super(props, context);
        
        this.state = {isEdit: false, isRequirements: false};
    }

    render():JSX.Element {
        return (
            <ReactMDL.Card shadow={2} className="backlog-story mdl-cell mdl-cell--12-col">
                <ReactMDL.CardTitle expand className="backlog-story-title-block">
                    {this.props.storyModel.get('text')}
                </ReactMDL.CardTitle>
            </ReactMDL.Card>
        );
    }
}

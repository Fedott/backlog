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
    tempStoryText: string;

    constructor(props: IStoryItemProps, context:any) {
        super(props, context);
        
        this.state = {isEdit: false, isRequirements: false};

        this.tempStoryText = this.props.storyModel.get('text');
    }

    textChangeHandler(event) {
        this.tempStoryText = event.target.value;
    }

    toggleEditMode() {
        this.setState({
            isEdit: !this.state.isEdit,
        });
    }

    saveStory() {
        this.props.storyModel.save({
            text: this.tempStoryText,
        });
        this.toggleEditMode();
    }

    cancelStory() {
        this.tempStoryText = this.props.storyModel.get('text');
        this.toggleEditMode();
    }

    render():JSX.Element {
        var title;
        if (this.state.isEdit) {
            title = (
                <ReactMDL.Textfield
                    maxRows={10}
                    defaultValue={this.props.storyModel.get('text')}
                    label="text"
                    onChange={this.textChangeHandler.bind(this)}
                />
            );
        } else {
            title = (
                <h4>
                    {this.props.storyModel.get('text')}
                </h4>
            );
        }

        var actions;
        if (this.state.isEdit) {
            actions = (
                <ReactMDL.CardActions>
                    <ReactMDL.Button onClick={this.saveStory.bind(this)}>
                        Сохранить
                    </ReactMDL.Button>
                    <ReactMDL.Button onClick={this.cancelStory.bind(this)}>
                        Отмена
                    </ReactMDL.Button>
                </ReactMDL.CardActions>
            );
        } else {
            actions = (
                <ReactMDL.CardActions>
                    <ReactMDL.Button onClick={this.toggleEditMode.bind(this)}>
                        Редактировать
                    </ReactMDL.Button>
                    <ReactMDL.Button>
                        Требования
                    </ReactMDL.Button>
                </ReactMDL.CardActions>
            );
        }

        return (
            <ReactMDL.Card shadow={2} className="backlog-story mdl-cell mdl-cell--12-col">
                <ReactMDL.CardTitle expand className="backlog-story-title">
                    {title}
                </ReactMDL.CardTitle>
                {actions}
            </ReactMDL.Card>
        );
    }
}

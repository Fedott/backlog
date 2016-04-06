import {StoryModel} from "../../StoryModel";
import * as React from 'react';
import * as ReactMDL from 'react-mdl'

export interface IStoryItemProps {
    storyModel: StoryModel;
    isEdit?: boolean;
    isRequirements?: boolean;
    onSave?:Function;
    onCancel?:Function;
}

export interface IStoryItemState {
    isEdit?: boolean;
    isRequirements?: boolean;
    rows?: number;
}

export class StoryItem extends React.Component<IStoryItemProps, IStoryItemState> {
    tempStoryText: string;

    constructor(props: IStoryItemProps, context:any) {
        super(props, context);

        this.tempStoryText = this.props.storyModel.get('text');

        this.state = {
            isEdit: props.isEdit || false,
            isRequirements: props.isRequirements || false,
            rows: this.calcRows(),
        };
    }

    calcRows():number {
        if (!this.tempStoryText) {
            return 3;
        }
        var newLines = this.tempStoryText.match(/\n/g);
        var rows = newLines ? newLines.length + 1 : 3;

        if (rows >= 3) {
            return rows;
        }

        return 3;
    }

    textChangeHandler(event) {
        this.tempStoryText = event.target.value;

        var rows = this.calcRows();
        if (rows != this.state.rows) {
            this.setState({rows: rows});
        }
    }

    toggleEditMode() {
        this.setState({
            isEdit: !this.state.isEdit,
        });
    }

    saveStory() {
        var isNew:boolean = this.props.storyModel.isNew();

        this.props.storyModel.save({
            text: this.tempStoryText,
        });

        this.toggleEditMode();

        if (isNew) {
            this.props.storyModel.collection.add(this.props.storyModel);
        }

        if (this.props.onSave) {
            this.props.onSave();
        }
    }

    cancelStory() {
        this.tempStoryText = this.props.storyModel.get('text');
        this.toggleEditMode();

        if (this.props.onCancel) {
            this.props.onCancel();
        }
    }

    toggleCompleteStory() {
        this.props.storyModel.set('completed', !this.props.storyModel.get('completed'));
        this.props.storyModel.save();

        this.forceUpdate();
    }

    nl2br(text: string) {
        var result;
        if (null == text) {
            result = <span />;
        } else {
            result = text.split("\n").map((part:string) => {
                return (
                    <span key={part}>
                    {part}
                        <br />
                </span>
                )
            });
        }

        return result;
    }

    render():JSX.Element {
        var title;
        if (this.state.isEdit) {
            title = (
                <ReactMDL.Textfield
                    maxRows={10}
                    rows={this.state.rows}
                    defaultValue={this.tempStoryText}
                    label="text"
                    onChange={this.textChangeHandler.bind(this)}
                />
            );
        } else {
            title = (
                <h4>
                    {this.nl2br(this.props.storyModel.get('text'))}
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

        var completeIcon;
        if (this.props.storyModel.get('completed')) {
            completeIcon = 'check_box';
        } else {
            completeIcon = 'check_box_outline_blank';
        }

        return (
            <ReactMDL.Card shadow={2} className="backlog-story mdl-cell mdl-cell--12-col">
                <ReactMDL.CardTitle expand className="backlog-story-title">
                    {title}
                </ReactMDL.CardTitle>
                {actions}
                <ReactMDL.CardMenu>
                    <ReactMDL.IconButton name={completeIcon} onClick={this.toggleCompleteStory.bind(this)} />
                </ReactMDL.CardMenu>
            </ReactMDL.Card>
        );
    }
}

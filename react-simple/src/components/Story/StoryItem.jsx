import * as React from "react";
import { DragSource, DropTarget } from 'react-dnd';

import StoryView from "./StoryView.jsx";
import StoryEditForm from "./StoryEditFrom.jsx";

const cardStorySourceContract = {
    beginDrag(props) {
        return {
            story: props.story,
            index: props.index,
        };
    }
};

const cardStoryTargetContract = {
    drop(props, monitor) {
        const item = monitor.getItem();

        props.moveCard(item.index, props.index);

        return { moved: true };
    }
};

function collectDragSource(connect, monitor) {
    return {
        connectDragSource: connect.dragSource(),
        isDragging: monitor.isDragging(),
    }
}

function collectDropTarget(connect, monitor) {
    return {
        connectDropTarget: connect.dropTarget(),
        isOver: monitor.isOver(),
    }
}

class StoryItem extends React.Component {
    static propTypes = {
        story: React.PropTypes.object,
        edit: React.PropTypes.bool,
        isCreateForm: React.PropTypes.bool,
        projectId: React.PropTypes.string.isRequired,

        index: React.PropTypes.number.isRequired,

        connectDropTarget: React.PropTypes.func,
        connectDragSource: React.PropTypes.func,
        isDragging: React.PropTypes.bool,
        isOver: React.PropTypes.bool,
        moveCard: React.PropTypes.func,

        onStoryCreatedCallback: React.PropTypes.func,
        onStoryCompleted: React.PropTypes.func.isRequired,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            story: props.story,
            projectId: props.projectId,
            edit: props.edit || false,
            isCreateForm: props.isCreateForm || false,
            isDeleted: false,
        };

        this.onChangeEdit = this.onChangeEdit.bind(this);
        this.onSaved = this.onSaved.bind(this);
        this.onDeleted = this.onDeleted.bind(this);
    }

    onChangeEdit() {
        this.setState({
            edit: !this.state.edit,
        })
    }

    onSaved(story) {
        if (this.state.isCreateForm) {
            this.props.onStoryCreatedCallback(story);
        } else {
            this.setState({
                edit: false,
            });
        }
    }

    onDeleted() {
        this.setState({
            isDeleted: true,
        });
    }

    render() {
        if (this.state.isDeleted) {
            return null;
        }

        const { isOver, isDragging, connectDragSource, connectDropTarget } = this.props;

        if (this.state.edit) {
            return <StoryEditForm
                story={this.state.story}
                projectId={this.state.projectId}
                onCancel={this.onChangeEdit}
                onSaved={this.onSaved}
                isCreateForm={this.state.isCreateForm}
            />;
        } else {
            return connectDragSource(connectDropTarget(
                <div className="mdl-cell mdl-cell--12-col">
                    {isOver && <hr className="storyDropTarget"/>}
                    <StoryView
                        story={this.state.story}
                        projectId={this.state.projectId}
                        onChangeEdit={this.onChangeEdit}
                        onDeleted={this.onDeleted}
                        isDragging={isDragging}
                        onCompleted={this.props.onStoryCompleted}
                    />
                </div>
            ));
        }
    }
}

export default DropTarget('card-story', cardStoryTargetContract, collectDropTarget)(DragSource('card-story', cardStorySourceContract, collectDragSource)(StoryItem));

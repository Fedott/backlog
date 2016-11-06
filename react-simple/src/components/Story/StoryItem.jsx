import * as React from "react";
import { findDOMNode } from 'react-dom';
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

        console.log(item.index, props.index);

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
    };

    constructor(props, context:any) {
        super(props, context);

        this.state = {
            story: props.story,
            projectId: props.projectId,
            edit: props.edit || false,
            isCreateForm: props.isCreateForm || false,
            isDeleted: false,
        };
    }

    onChangeEdit() {
        this.setState({
            edit: !this.state.edit,
        })
    }

    onSaved(story) {
        this.setState({
            story: story,
            edit: false,
            isCreateForm: false,
        })
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

        var style = null;
        if (this.state.edit) {
            return <StoryEditForm
                story={this.state.story}
                projectId={this.state.projectId}
                onCancel={this.onChangeEdit.bind(this)}
                onSaved={this.onSaved.bind(this)}
                isCreateForm={this.state.isCreateForm}
            />;
        } else {
            return connectDragSource(connectDropTarget(
                <div className="mdl-cell mdl-cell--12-col">
                    {isOver && <hr className="storyDropTarget"/>}
                    <StoryView
                        story={this.state.story}
                        projectId={this.state.projectId}
                        onChangeEdit={this.onChangeEdit.bind(this)}
                        onDeleted={this.onDeleted.bind(this)}
                        isDragging={isDragging}
                    />
                </div>
            ));
        }
    }
}

export default DropTarget('card-story', cardStoryTargetContract, collectDropTarget)(DragSource('card-story', cardStorySourceContract, collectDragSource)(StoryItem));

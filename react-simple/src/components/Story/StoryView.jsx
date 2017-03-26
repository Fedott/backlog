import * as React from "react";
import {
    Card,
    CardActions,
    CardText,
    CardTitle,
    FlatButton,
    Divider
} from 'material-ui';
import nl2br from 'react-nl2br';
import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';
import RequirementsList from "./Requirement/RequirementsList.jsx";

class StoryView extends React.Component {
    static propTypes = {
        story: React.PropTypes.object,
        onChangeEdit: React.PropTypes.func,
        onChangeRequirements: React.PropTypes.func,
        onDeleted: React.PropTypes.func,
        onCompleted: React.PropTypes.func.isRequired,
        isDragging: React.PropTypes.bool,
    };

    constructor(props, context) {
        super(props, context);

        this.state = {
            story: props.story,
            isDragging: props.isDragging,
            isRequirements: false,
        };

        this.onChangeEdit = props.onChangeEdit || (() => {});
        this.onChangeRequirements = props.onChangeRequirements || (() => {});
        this.onDeleted = props.onDeleted || (() => {});

        this.onDelete = this.onDelete.bind(this);
        this.onMarkAsCompleted = this.onMarkAsCompleted.bind(this);
        this.toggleShowRequirements = this.toggleShowRequirements.bind(this);
    }

    async onDelete() {
        await webSocketClient.sendRequest({
            type: "delete-story",
            payload: {
                storyId: this.state.story.id,
            },
        });

        this.onDeleted();
    }

    async onMarkAsCompleted() {
        const response = await webSocketClient.sendRequest({
            type: 'story-mark-as-completed',
            payload: {
                storyId: this.state.story.id,
            }
        });

        if (response.type !== 'error') {
            this.state.story.isCompleted = true;
            this.props.onCompleted(this.state.story);
        }
    }

    toggleShowRequirements() {
        this.setState({
            isRequirements: !this.state.isRequirements,
        });
    }

    componentWillReceiveProps(nextProps) {
        if (undefined !== nextProps.isDragging || undefined !== nextProps.isOver ) {
            this.setState({
                isDragging: nextProps.isDragging,
            });
        }
    }

    render() {
        let style = {};
        if (this.state.isDragging) {
            style = {opacity: 0.1}
        }

        let cardText;
        if (this.state.isRequirements) {
            cardText = <RequirementsList storyId={this.state.story.id}/>
        } else {
            cardText = nl2br(this.state.story.text);
        }

        return (
            <Card className="backlog-story" style={style} data-story-id={this.state.story.id}>
                <CardTitle title={this.state.story.title} className="backlog-story-title"/>
                <CardText>
                    {cardText}
                </CardText>
                <Divider />
                <CardActions showExpandableButton={true}>
                    <FlatButton label="Редактировать" onTouchTap={this.onChangeEdit} />
                    <FlatButton label="Требования" onTouchTap={this.toggleShowRequirements} />
                    <FlatButton label="Пометить готовой" onTouchTap={this.onMarkAsCompleted} />
                </CardActions>
                <CardActions expandable={true}>
                    <FlatButton label="Удалить" onTouchTap={this.onDelete} />
                </CardActions>
            </Card>
        );
    }
}

export default StoryView;

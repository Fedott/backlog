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
        };

        this.onChangeEdit = props.onChangeEdit || (() => {});
        this.onChangeRequirements = props.onChangeRequirements || (() => {});
        this.onDeleted = props.onDeleted || (() => {});

        this.onDelete = this.onDelete.bind(this);
        this.onMarkAsCompleted = this.onMarkAsCompleted.bind(this);
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

        return (
            <Card className="backlog-story" style={style}>
                <CardTitle title={this.state.story.title} className="backlog-story-title"/>
                <CardText>
                    {nl2br(this.state.story.text)}
                </CardText>
                <Divider />
                <CardActions showExpandableButton={true}>
                    <FlatButton label="Редактировать" onTouchTap={this.onChangeEdit} />
                    <FlatButton label="Требования" />
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

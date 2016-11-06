import * as React from "react";
import * as ReactMDL from 'react-mdl';
import nl2br from 'react-nl2br';
import webSocketClient from '../../libraries/WebSocket/WebSocketClient.js';

class StoryView extends React.Component {
    static propTypes = {
        story: React.PropTypes.object,
        onChangeEdit: React.PropTypes.func,
        onChangeRequirements: React.PropTypes.func,
        onDeleted: React.PropTypes.func,
        isDragging: React.PropTypes.bool,
    };

    constructor(props, context:any) {
        super(props, context);

        this.state = {
            story: props.story,
            isDragging: props.isDragging,
        };

        this.onChangeEdit = props.onChangeEdit || (() => {});
        this.onChangeRequirements = props.onChangeRequirements || (() => {});
        this.onDeleted = props.onDeleted || (() => {});
    }

    async onDelete() {
        var response = await webSocketClient.sendRequest({
            type: "delete-story",
            payload: {storyId: this.state.story.id},
        });

        this.onDeleted();
    }

    componentWillReceiveProps(nextProps) {
        if (undefined != nextProps.isDragging || undefined != nextProps.isOver ) {
            this.setState({
                isDragging: nextProps.isDragging,
            });
        }
    }

    render() {
        var style = {};
        if (this.state.isDragging) {
            style = {opacity: 0.1}
        }
        return (
            <ReactMDL.Card shadow={this.state.isDragging ? 7 : 2} className="backlog-story" style={style}>
                <ReactMDL.CardTitle expand className="backlog-story-title">
                    {this.state.story.title}
                </ReactMDL.CardTitle>
                <ReactMDL.CardText>
                    {nl2br(this.state.story.text)}
                </ReactMDL.CardText>

                <ReactMDL.CardActions border>
                    <ReactMDL.Button onClick={this.onChangeEdit}>
                        Редактировать
                    </ReactMDL.Button>
                    <ReactMDL.Button>
                        Требования
                    </ReactMDL.Button>
                </ReactMDL.CardActions>

                <ReactMDL.CardMenu>
                    <ReactMDL.IconButton name='more_vert' id={"card-story-menu" + this.state.story.id} />
                    <ReactMDL.Menu
                        target={"card-story-menu" + this.state.story.id}
                        align="right"
                        ripple
                    >
                        <ReactMDL.MenuItem
                            onClick={this.onDelete.bind(this)}
                        >Delete</ReactMDL.MenuItem>
                    </ReactMDL.Menu>
                </ReactMDL.CardMenu>
            </ReactMDL.Card>
        );
    }
}

export default StoryView;

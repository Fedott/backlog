import * as React from "react";
import * as ReactMDL from 'react-mdl';
import nl2br from 'react-nl2br';

class StoryItem extends React.Component {
    constructor(props, context:any) {
        super(props, context);

        this.state = {
            story: props.story,
        };
    }

    render() {
        return (
            <ReactMDL.Card shadow={2} className="backlog-story mdl-cell mdl-cell--12-col">
                <ReactMDL.CardTitle expand className="backlog-story-title">
                    {this.state.story.title}
                </ReactMDL.CardTitle>
                <ReactMDL.CardText>
                    {nl2br(this.state.story.text)}
                </ReactMDL.CardText>

                <ReactMDL.CardActions border>
                    <ReactMDL.Button>
                        Редактировать
                    </ReactMDL.Button>
                    <ReactMDL.Button>
                        Требования
                    </ReactMDL.Button>
                </ReactMDL.CardActions>

                <ReactMDL.CardMenu>
                    <ReactMDL.IconButton name='check_box_outline_blank' />
                </ReactMDL.CardMenu>
            </ReactMDL.Card>
        );
    }
}

export default StoryItem;

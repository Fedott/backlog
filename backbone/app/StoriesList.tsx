import {StoriesCollection} from "./StoriesCollection";
import * as React from 'react';
import * as ReactMDL from 'react-mdl'
import {StoryModel} from "./StoryModel";
import {StoryItem} from "./StoryItem";

export interface IStoriesListState {
    storiesCollection: StoriesCollection;
}

export interface IStoriesListProps {}

export class StoriesList extends React.Component<IStoriesListProps, IStoriesListState> {
    constructor(props: IStoriesListProps, context: any) {
        super(props, context);

        this.state = {storiesCollection: new StoriesCollection()};
    }

    componentDidMount():void {
        this.state.storiesCollection.on('add remove change', this.forceUpdate.bind(this, null));
        this.state.storiesCollection.fetch();
    }

    componentWillUnmount():void {
        this.state.storiesCollection.off(null, null, this);
    }

    render() {
        var stories = this.state.storiesCollection.map((story:StoryModel) => {
            return <StoryItem storyModel={story} key={story.id} />
        });
        
        return (
            <div className="backlog-list mdl-grid">
                {stories}
            </div>
        );
    }
}

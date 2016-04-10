import {StoriesCollection} from "./../../StoriesCollection";
import * as React from 'react';
import * as ReactMDL from 'react-mdl'
import {StoryModel} from "./../../StoryModel";
import {StoryItem} from "./StoryItem";
import {StoryFilter} from "../Application";

export interface IStoriesListState {
    storiesCollection?: StoriesCollection;
    createForm?: boolean;
    filter?: StoryFilter;
}

export interface IStoriesListProps {
    createForm?:boolean;
    onChangeCreateForm?:Function;
    statusFilter?: StoryFilter;
}

export class StoriesList extends React.Component<IStoriesListProps, IStoriesListState> {
    constructor(props: IStoriesListProps, context: any) {
        super(props, context);

        this.state = {
            storiesCollection: new StoriesCollection(),
            createForm: props.createForm || false,
            filter: props.statusFilter || StoryFilter.All,
        };
    }

    componentDidMount():void {
        this.state.storiesCollection.setStatusFilter(this.state.filter);
        this.state.storiesCollection.on('add remove change', this.forceUpdate.bind(this, null));
        this.state.storiesCollection.fetch();
    }


    componentWillUpdate(nextProps:IStoriesListProps, nextState:IStoriesListState, nextContext:any):void {
        if (this.state.storiesCollection.statusFilter != nextProps.statusFilter) {
            this.state.storiesCollection.setStatusFilter(nextProps.statusFilter);
            this.state.storiesCollection.fetch();
        }
    }

    componentWillUnmount():void {
        this.state.storiesCollection.off(null, null, this);
    }

    toggleCreateForm() {
        this.setState({
            createForm: !this.state.createForm,
        });

        if (this.props.onChangeCreateForm) {
            this.props.onChangeCreateForm(this.state.createForm);
        }
    }

    render() {
        var createForm;
        if (this.props.createForm) {
            var newStory = new StoryModel();
            newStory.collection = this.state.storiesCollection;
            createForm = <StoryItem
                storyModel={newStory}
                isEdit={true}
                onSave={this.toggleCreateForm.bind(this)}
                onCancel={this.toggleCreateForm.bind(this)}
            />
        }

        var stories = this.state.storiesCollection.map((story:StoryModel) => {
            return <StoryItem storyModel={story} key={story.id} />
        });

        return (
            <div className="backlog-list mdl-grid">
                {createForm}
                {stories}
            </div>
        );
    }
}
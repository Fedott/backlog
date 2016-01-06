import {StoryModel} from "./StoryModel";
import {StoryView} from "./StoryView";
import {StoriesCollection} from "./StoriesCollection";
import template from "./templates/storiesList";

export class StoriesView extends Backbone.View<StoryModel> {
    public template;
    collection: Backbone.Collection<StoryModel> = new StoriesCollection();

    constructor() {
        super();

        this.tagName = "backlog";
        this.setElement($('backlog'), true);

        this.render();

        _.bindAll(this, 'addOne', 'addAll', 'render');

        this.collection.bind('add', this.addOne);
        this.collection.bind('reset', this.addAll);

        this.collection.fetch();
    }

    render():Backbone.View<StoryModel> {
        this.$el.html(template({}));

        return this;
    }

    addOne(story: StoryModel) {
        var view = new StoryView({model: story});
        this.$('.backlog-list').append(view.render().el);
        (<any>window).componentHandler.upgradeAllRegistered();
    }

    addAll() {
        this.collection.each(this.addOne);
        (<any>window).componentHandler.upgradeAllRegistered();
    }
}

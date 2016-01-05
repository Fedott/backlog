import {StoryModel} from "./StoryModel";
import {StoryView} from "./StoryView";

export class AppView extends Backbone.View<StoryModel> {
    public template;

    constructor() {
        super();

        this.tagName = "backlog";
        this.setElement($('backlog'), true);
        this.render();
    }

    render():Backbone.View<StoryModel> {
        var storyView = new StoryView({model: new StoryModel()});
        this.$('.backlog-list').append(storyView.render().el);

        return this;
    }
}

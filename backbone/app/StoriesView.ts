import {StoryModel} from "./StoryModel";
import {StoryView} from "./StoryView";
import {StoriesCollection} from "./StoriesCollection";
import template from "./templates/storiesList";

export class StoriesView extends Backbone.View<StoryModel> {
    public template;
    collection: Backbone.Collection<StoryModel> = new StoriesCollection();
    public state;
    protected createForm: StoryView;

    constructor() {
        super();

        this.tagName = "backlog";
        this.setElement($('backlog'), true);

        this.state = {
            createForm: false,
        };

        this.render();

        _.bindAll(this, 'addOne', 'addAll', 'render', 'toggleCreateForm');

        this.collection.bind('add', this.addOne);
        this.collection.bind('reset', this.addAll);

        this.collection.fetch();
    }

    events():Backbone.EventsHash {
        return {
            'click #add-story-button': 'toggleCreateForm',
        };
    }

    render():Backbone.View<StoryModel> {
        this.$el.html(template({}));

        return this;
    }

    addOne(story: StoryModel) {
        var view = new StoryView({model: story});
        this.$('.backlog-list').prepend(view.render().el);
        (<any>window).componentHandler.upgradeAllRegistered();
    }

    addAll() {
        this.collection.each(this.addOne);
        (<any>window).componentHandler.upgradeAllRegistered();
    }

    toggleCreateForm() {
        if (null == this.createForm) {
            this.createForm = new StoryView({model: new StoryModel(), isEditMode: true});
            this.createForm.model.on('sync', this.addOne);
            this.createForm.model.on('sync', this.toggleCreateForm);
            this.$('.backlog-list').prepend(this.createForm.el);
            this.createForm.render();
            (<any>window).componentHandler.upgradeAllRegistered();
        } else {
            this.createForm.model.off();
            this.createForm.remove();
            this.createForm = null;
        }
    }
}

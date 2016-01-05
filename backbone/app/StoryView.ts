import {StoryModel} from "./StoryModel";
import template from './templates/story';

export class StoryView extends Backbone.View<StoryModel> {
    public template;
    public state;

    constructor(options?) {
        this.className = 'backlog-story mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col';
        this.tagName = 'div';

        super(options);

        this.state = {
            isEditMode: false,
            showText: true
        };

        this.template = template;
    }

    events():Backbone.EventsHash {
        return {
            'click .edit': 'toggleEdit',
            'click .save': 'save',
            'click .cancel': 'cancel',
        };
    }

    toggleEdit() {
        this.state.isEditMode = !this.state.isEditMode;
        this.render();
    }

    save() {
        var data = Backbone.Syphon.serialize(this);
        this.model.set(data);
        this.model.save();
        this.toggleEdit();
    }

    cancel() {
        this.toggleEdit();
    }

    render():Backbone.View<StoryModel> {
        this.$el.html(this.template({
            story: this.model.attributes,
            state: this.state
        }));

        return this;
    }
}
import {StoryModel} from "./StoryModel";
import template from './templates/story';
import ViewOptions = Backbone.ViewOptions;

export class StoryView extends Backbone.View<StoryModel> {
    public template;
    public state;

    constructor(options?) {
        this.className = 'backlog-story mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col';
        this.tagName = 'div';

        super(options);

        this.state = {
            isEditMode: options.isEditMode || false,
            showText: options.showText || true
        };

        this.template = template;
    }

    initialize(options?: ViewOptions<StoryModel>): void {
        _.bindAll(this, 'render');
        this.model.bind('sync', this.render);
        this.model.requirements.bind('change', this.render);
    }

    events():Backbone.EventsHash {
        return {
            'click .edit': 'toggleEdit',
            'click .requirements': 'toggleView',
            'click .save': 'save',
            'click .cancel': 'cancel',
            'click .add-requirement': 'addRequirement',
            'click .mark-as-completed': 'markAsCompleted',
            'keyup #name': 'addRequirementEnter',
        };
    }

    toggleView() {
        this.state.showText = !this.state.showText;
        this.render();
    }

    toggleEdit() {
        if (this.state.isEditMode) {
            var a = autosize.destroy(this.el.querySelector('#text'));
        }

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

    addRequirementEnter(event) {
        if (13 == event.keyCode) {
            this.addRequirement();
        }
    }

    addRequirement() {
        var requirementName = this.$("#name").val();
        this.model.requirements.create({
            name: requirementName,
        }, {wait: true});
        this.render();
    }

    markAsCompleted() {
        this.model.set('completed', !this.model.get('completed'));
        this.model.save();
    }

    render():Backbone.View<StoryModel> {
        this.$el.html(this.template({
            story: this.model.attributes,
            state: this.state
        }));

        (<any>window).componentHandler.upgradeAllRegistered();

        if (this.state.isEditMode) {
            autosize(this.el.querySelector('#text'));
            this.$('#text').focus();
        }

        return this;
    }
}
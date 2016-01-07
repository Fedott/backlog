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
            'click .requirements': 'toggleView',
            'click .save': 'save',
            'click .cancel': 'cancel',
            'click .add-requirement': 'addRequirement',
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

        if (this.state.isEditMode) {
            autosize(this.el.querySelector('#text'));
            this.$('#text').focus();
        }
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
        });
        this.render();
    }

    render():Backbone.View<StoryModel> {
        this.$el.html(this.template({
            story: this.model.attributes,
            state: this.state
        }));

        (<any>window).componentHandler.upgradeAllRegistered();

        return this;
    }
}
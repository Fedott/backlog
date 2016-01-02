import {Component} from 'angular2/core';
import {Story} from "./story";
import {Input} from "angular2/core";
import {Nl2BrPipe} from "./pipes/nl2br.pipe";
import {ElementRef} from "angular2/core";
import {StoryService} from "./story.service";

@Component({
    selector: 'backlog-story',
    inputs: ['story', 'isEditMode'],
    templateUrl: 'app/templates/story.component.html',
    pipes: [Nl2BrPipe]
})
export class StoryComponent {
    story: Story;

    showBasic = true;
    showRequirements = false;
    showDetails = false;

    isEditMode = false;
    textHeight: String;

    element: ElementRef;

    constructor (myElement: ElementRef) {
        this.element = myElement;
    }

    toggleShowDetails () {
        this.showDetails = !this.showDetails;
    }

    toggleShowRequirements () {
        this.showRequirements = !this.showRequirements;
        this.showBasic = !this.showBasic;
    }

    toggleEditMode() {
        if (!this.isEditMode) {
            this.textHeight = this.element.nativeElement.querySelector('.backlog-story-title-block h4').scrollHeight;
        }

        this.isEditMode = !this.isEditMode;
    }

    autoExpand(target) {
        if (target instanceof HTMLElement) {
            var calcHeight = target.scrollHeight - 8;
            target.style.height = calcHeight.toString() + "px";
        }
    }

    getTextHeight() {
        if (null !== this.textHeight) {
            return this.textHeight + 'px';
        }

        return '';
    }

    save() {
        if (null == this.story.id) {
            //this._storyService.createStory(this.story);
        }
        this.toggleEditMode();
    }

    cancel() {
        this.toggleEditMode();
    }
}

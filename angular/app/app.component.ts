import {Component} from 'angular2/core';
import {Story} from "./story";
import {StoryComponent} from "./story.component"
import {StoryService} from "./story.service";
import {OnInit} from "angular2/core";

@Component({
    selector: 'my-app',
    directives: [StoryComponent],
    providers: [StoryService],
    template: `
        <div class="backlog-list mdl-grid">
            <backlog-story
                *ngIf="isAddStoryFormShow"
                [story]="newStory"
                [isEditMode]="true"
                [storyService]="storyService"
                (saveEvent)="hideAddForm()"
                (cancelEvent)="hideAddForm()"
                ></backlog-story>
            <backlog-story *ngFor="#story of stories" [story]="story"></backlog-story>
        </div>

        <button (click)="showAddForm()" id="add-story-button" class="mdl-button mdl-js-button mdl-button--fab mdl-button--colored">
            <i class="material-icons">add</i>
        </button>
        `
})
export class AppComponent implements OnInit {
    public title = 'Backlog';
    public stories: Story[];

    public newStory: Story;

    public isAddStoryFormShow = false;

    constructor (public storyService: StoryService) { }

    ngOnInit() {
        this.storyService.getStories().then((stories) => this.stories = stories);
    }

    showAddForm() {
        this.newStory = new Story;
        this.isAddStoryFormShow = true;
    }

    hideAddForm() {
        this.isAddStoryFormShow = false;
    }
}

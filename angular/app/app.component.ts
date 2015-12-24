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
            <backlog-story *ngFor="#story of stories" [story]="story"></backlog-story>
        </div>
        `
})
export class AppComponent implements OnInit {
    public title = 'Backlog';
    public stories: Story[];

    constructor (private _storyService: StoryService) { }

    ngOnInit() {
        this.stories = this._storyService.getStories();
    }
}

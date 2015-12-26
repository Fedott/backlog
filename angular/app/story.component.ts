import {Component} from 'angular2/core';
import {Story} from "./story";
import {Input} from "angular2/core";
import {Nl2BrPipe} from "./pipes/nl2br.pipe";

@Component({
    selector: 'backlog-story',
    inputs: ['story'],
    templateUrl: 'app/templates/story.component.html',
    pipes: [Nl2BrPipe]
})
export class StoryComponent{
    story: Story;

    showBasic = true;
    showRequirements = false;
    showDetails = false;

    toggleShowDetails () {
        this.showDetails = !this.showDetails;
    }

    toggleShowRequirements () {
        this.showRequirements = !this.showRequirements;
        this.showBasic = !this.showBasic;
    }
}

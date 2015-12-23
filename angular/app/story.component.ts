import {Component} from 'angular2/core';
import {Story} from "./story";
import {Input} from "angular2/core";

@Component({
    selector: 'backlog-story',
    inputs: ['story'],
    template: `
        <div class="backlog-story mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col">
            <div class="mdl-card__title mdl-card--expand backlog-story-title">
                <div [class.show]="showBasic == true" class="backlog-story-basic">
                    <h4>{{story.text}}</h4>
                </div>
                <div [class.show]="showRequirements == true" class="backlog-story-requirements">
                    <h5>Требования</h5>
                    <ul>
                        <li *ngFor="#requirement of story.requirements">
                            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="checkbox-">
                                <input type="checkbox" id="checkbox-" class="mdl-checkbox__input">
                                <span class="mdl-checkbox__label">{{requirement}}</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mdl-card__actions mdl-card--border">
                <a (click)="toggleShowDetails()" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
                    Редактировать
                </a>
                <a (click)="toggleShowRequirements()" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
                    Требования
                </a>
            </div>
            <div class="mdl-card__menu">
                <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect">
                    <i class="material-icons">assignment_late</i>
                </button>
            </div>
        </div>
    `
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

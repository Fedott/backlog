import {Component} from 'angular2/core';

var STORIES: Story[] = [
    {id: 1, text: 'Как владелец продукта, я хочу нормальный бэклог, что бы можно было работать', requirements: ['Дизайн', 'Вёрстка', 'Переводы'], showRequirements: false},
    {id: 2, text: 'Как владелец продукта, я хочу иметь возможность редактировать истории, что бы исправлять ошибки', requirements: ['qwe'], showRequirements: false},
    {id: 3, text: 'Как владелец продукта, я хочу создавать новые истории, что бы контролировать бэклог', requirements: ['qwe'], showRequirements: false},
];

@Component({
    selector: 'my-app',
    template: `
        <div *ngFor="#story of stories"
            [class.show-requirements]="story.showRequirements == true"
            class="backlog-story mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col">
            <div class="mdl-card__title mdl-card--expand backlog-story-basic">
                <h4>{{story.text}}</h4>
            </div>
            <div class="mdl-card__title mdl-card--expand backlog-story-requirements">
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
            <div class="mdl-card__actions mdl-card--border">
                <a (click)="editStory(story)" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
                    Редактировать
                </a>
                <a (click)="showRequirements(story)" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
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
export class AppComponent {
    public title = 'Backlog';
    public stories = STORIES;
    public selectedStory: Story;

    editStory (story: Story) {
        this.selectedStory = story;
        console.log(this.selectedStory.text);
    }

    showRequirements (story: Story) {
        story.showRequirements = !story.showRequirements;
    }
}

interface Story {
    id: number;
    text: string;
    requirements: string[];
    showRequirements: boolean;
}
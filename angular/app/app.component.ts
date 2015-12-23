import {Component} from 'angular2/core';
import {Story} from "./story";
import {StoryComponent} from "./story.component"

var STORIES: Story[] = [
    {id: 1, text: 'Как владелец продукта, я хочу нормальный бэклог, что бы можно было работать', requirements: ['Дизайн', 'Вёрстка', 'Переводы']},
    {id: 2, text: 'Как владелец продукта, я хочу иметь возможность редактировать истории, что бы исправлять ошибки', requirements: ['qwe']},
    {id: 3, text: 'Как владелец продукта, я хочу создавать новые истории, что бы контролировать бэклог', requirements: ['qwe']},
];

@Component({
    selector: 'my-app',
    directives: [StoryComponent],
    template: `
        <div class="backlog-list mdl-grid">
            <backlog-story *ngFor="#story of stories" [story]="story"></backlog-story>
        </div>
        `
})
export class AppComponent {
    public title = 'Backlog';
    public stories = STORIES;
}

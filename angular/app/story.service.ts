import {Injectable} from "angular2/core";

var STORIES: Story[] = [
    {id: 1, text: 'Как владелец продукта, я хочу нормальный бэклог, что бы можно было работать', requirements: ['Дизайн', 'Вёрстка', 'Переводы']},
    {id: 2, text: 'Как владелец продукта, я хочу иметь возможность редактировать истории, что бы исправлять ошибки', requirements: ['qwe']},
    {id: 3, text: 'Как владелец продукта, я хочу создавать новые истории, что бы контролировать бэклог', requirements: ['qwe']},
];

@Injectable()
export class StoryService {
    getStories() {
        return STORIES;
    }
}
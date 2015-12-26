import {Injectable} from "angular2/core";
import {Story} from "./story";

var STORIES: Story[] = [
    {id: 1, text: 'Как владелец продукта,\n я хочу нормальный бэклог,\n что бы можно было работать', requirements: ['Дизайн', 'Вёрстка', 'Переводы']},
    {id: 2, text: 'Как владелец продукта,\n я хочу иметь возможность редактировать истории,\n что бы исправлять ошибки', requirements: ['qwe']},
    {id: 3, text: 'Как владелец продукта,\n я хочу создавать новые истории,\n что бы контролировать бэклог', requirements: ['qwe']},
];

@Injectable()
export class StoryService {
    getStories() {
        return STORIES;
    }
}
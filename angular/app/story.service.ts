import {Injectable} from "angular2/core";
import {Story} from "./story";

var STORIES: Story[] = [
    {id: 1, text: 'Как владелец продукта,\nя хочу нормальный бэклог,\nчто бы можно было работать', requirements: ['Дизайн', 'Вёрстка', 'Переводы']},
    {id: 2, text: 'Как владелец продукта,\nя хочу иметь возможность редактировать истории,\nчто бы исправлять ошибки', requirements: ['qwe']},
    {id: 3, text: 'Как владелец продукта,\nя хочу создавать новые истории,\nчто бы контролировать бэклог', requirements: ['qwe']},
];

@Injectable()
export class StoryService {
    getStories() {
        return Promise.resolve(STORIES);
    }

    public createStory (story: Story) { return story; };
}
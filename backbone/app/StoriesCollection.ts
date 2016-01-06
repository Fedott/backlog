import {StoryModel} from "./StoryModel";
import Config from "./config";

export class StoriesCollection extends Backbone.Collection<StoryModel> {
    model = StoryModel;

    url = Config.apiUrl + 'stories';
}

import {RequirementModel} from "./RequirementModel";
import Config from "./config";
import {StoryModel} from "./StoryModel";

export class RequirementsCollection extends Backbone.Collection<RequirementModel> {
    story: StoryModel;

    model = RequirementModel;

    url = function() {
        return Config.apiUrl + 'stories/' + this.story.id + '/requirements';
    };

    initialize(models?: StoryModel[] | Object[], options?: any): void {
        this.story = options.story || null;
    };
}

import Config from './config';
import {RequirementsCollection} from "./RequirementsCollection";

export class StoryModel extends Backbone.Model {
    public requirements: RequirementsCollection;

    defaults() {
        return {
            text: null,
        };
    }

    constructor(attributes?: any, options?: any) {
        this.requirements = new RequirementsCollection([], {story: this});

        super(attributes, options);
    }

    initialize() {
        if (!this.get('text')) {
            this.set({ 'text': this.defaults().text });
        }

        this.urlRoot = Config.apiUrl + 'stories';
    }

    parse(response:any, options?:any):any {
        this.requirements.reset(response['requirements']);

        return super.parse(response, options);
    }
}

import Config from './config';
import {RequirementsCollection} from "./RequirementsCollection";

export class StoryModel extends Backbone.Model {
    public requirements: RequirementsCollection;

    blacklistProperties() {
        return [
            'id',
            'requirements',
        ];
    }

    defaults() {
        return {
            text: null,
            completed: false,
        };
    }

    constructor(attributes?: any, options?: any) {
        super(attributes, options);
    }

    initialize() {
        this.initRequirementCollection();
        
        if (!this.get('text')) {
            this.set({ 'text': this.defaults().text });
        }
        if (!this.get('completed')) {
            this.set({ 'completed': this.defaults().completed });
        }

        this.urlRoot = Config.apiUrl + 'stories';
    }

    parse(response:any, options?:any):any {
        this.initRequirementCollection();
        
        this.requirements.reset(response['requirements']);

        return super.parse(response, options);
    }

    protected initRequirementCollection() {
        if (!this.requirements) {
            this.requirements = new RequirementsCollection([], {story: this});
        }
    };

    save(attributes?:any, options?:ModelSaveOptions):any {
        options = options || {};
        options.isSave = true;

        return super.save(attributes, options);
    }

    toJSON(options?:any):any {
        if (options.isSave) {
            return _.omit(super.toJSON(options), this.blacklistProperties());
        }

        return super.toJSON(options);
    }
}

interface ModelSaveOptions extends Backbone.ModelSaveOptions {
    isSave?: boolean;
}
 
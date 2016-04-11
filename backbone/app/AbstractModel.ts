
export interface ModelSaveOptions extends Backbone.ModelSaveOptions {
    isSave?: boolean;
}

export abstract class AbstractModel extends Backbone.Model {
    abstract blacklistProperties();

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

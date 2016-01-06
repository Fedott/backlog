import Config from './config';

export class StoryModel extends Backbone.Model {
    defaults() {
        return {
            text: null
        };
    }

    initialize() {
        if (!this.get('text')) {
            this.set({ 'text': this.defaults().text });
        }

        this.urlRoot = Config.apiUrl + 'stories';
    }
}

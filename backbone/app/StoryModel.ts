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

        this.url = function() {
            if (this.id) {
                return null;
            }

            return Config.apiUrl + 'stories';
        }
    }
}

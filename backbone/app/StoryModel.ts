export class StoryModel extends Backbone.Model {
    defaults() {
        return {
            text: 'AZAZAZA'
        };
    }

    initialize() {
        if (!this.get('text')) {
            this.set({ 'text': this.defaults().text });
        }
    }
}
export class RequirementModel extends Backbone.Model {
    defaults() {
        return {
            name: null,
            isComplete: false,
        };
    }

    initialize() {
        if (!this.get('name')) {
            this.set({ 'name': this.defaults().name });
        }
        if (!this.get('isComplete')) {
            this.set({ 'isComplete': this.defaults().isComplete });
        }
    }
}
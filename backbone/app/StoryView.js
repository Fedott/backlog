System.register(['./templates/story'], function(exports_1) {
    var __extends = (this && this.__extends) || function (d, b) {
        for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
    var story_1;
    var StoryView;
    return {
        setters:[
            function (story_1_1) {
                story_1 = story_1_1;
            }],
        execute: function() {
            StoryView = (function (_super) {
                __extends(StoryView, _super);
                function StoryView(options) {
                    this.className = 'backlog-story mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col';
                    this.tagName = 'div';
                    _super.call(this, options);
                    this.state = {
                        isEditMode: false,
                        showText: true
                    };
                    this.template = story_1.default;
                }
                StoryView.prototype.events = function () {
                    return {
                        'click .edit': 'toggleEdit',
                        'click .save': 'save',
                        'click .cancel': 'cancel',
                    };
                };
                StoryView.prototype.toggleEdit = function () {
                    this.state.isEditMode = !this.state.isEditMode;
                    this.render();
                };
                StoryView.prototype.save = function () {
                    var data = Backbone.Syphon.serialize(this);
                    this.model.set(data);
                    this.model.save();
                    this.toggleEdit();
                };
                StoryView.prototype.cancel = function () {
                    this.toggleEdit();
                };
                StoryView.prototype.render = function () {
                    this.$el.html(this.template({
                        story: this.model.attributes,
                        state: this.state
                    }));
                    return this;
                };
                return StoryView;
            })(Backbone.View);
            exports_1("StoryView", StoryView);
        }
    }
});
//# sourceMappingURL=StoryView.js.map
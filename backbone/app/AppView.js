System.register(["./StoryModel", "./StoryView"], function(exports_1) {
    var __extends = (this && this.__extends) || function (d, b) {
        for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
    var StoryModel_1, StoryView_1;
    var AppView;
    return {
        setters:[
            function (StoryModel_1_1) {
                StoryModel_1 = StoryModel_1_1;
            },
            function (StoryView_1_1) {
                StoryView_1 = StoryView_1_1;
            }],
        execute: function() {
            AppView = (function (_super) {
                __extends(AppView, _super);
                function AppView() {
                    _super.call(this);
                    this.tagName = "backlog";
                    this.setElement($('backlog'), true);
                    this.render();
                }
                AppView.prototype.render = function () {
                    var storyView = new StoryView_1.StoryView({ model: new StoryModel_1.StoryModel() });
                    this.$('.backlog-list').append(storyView.render().el);
                    return this;
                };
                return AppView;
            })(Backbone.View);
            exports_1("AppView", AppView);
        }
    }
});
//# sourceMappingURL=AppView.js.map
System.register([], function(exports_1) {
    var __extends = (this && this.__extends) || function (d, b) {
        for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
    var StoryModel;
    return {
        setters:[],
        execute: function() {
            StoryModel = (function (_super) {
                __extends(StoryModel, _super);
                function StoryModel() {
                    _super.apply(this, arguments);
                }
                StoryModel.prototype.defaults = function () {
                    return {
                        text: 'AZAZAZA'
                    };
                };
                StoryModel.prototype.initialize = function () {
                    if (!this.get('text')) {
                        this.set({ 'text': this.defaults().text });
                    }
                };
                return StoryModel;
            })(Backbone.Model);
            exports_1("StoryModel", StoryModel);
        }
    }
});
//# sourceMappingURL=StoryModel.js.map
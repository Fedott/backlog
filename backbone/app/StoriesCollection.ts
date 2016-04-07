import {StoryModel} from "./StoryModel";
import Config from "./config";
import {StoryFilter} from "./components/Application";

export class StoriesCollection extends Backbone.Collection<StoryModel> {
    model = StoryModel;
    statusFilter: StoryFilter;

    get url() {
        var url = Config.apiUrl + 'stories';

        if (StoryFilter.Completed == this.statusFilter) {
            url += "?completed=true";
        } else if (StoryFilter.NotCompleted == this.statusFilter) {
            url += "?completed=false";
        }

        return url;
    }

    setStatusFilter(filter: StoryFilter) {
        this.statusFilter = filter;
    }
}

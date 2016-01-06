import {StoriesView} from "./StoriesView";
import * as hbs from 'handlebars';

hbs.registerHelper('nl2br', function(text) {
    if (null == text) {
        return null;
    }
    var nl2br = (text + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
    return new hbs.SafeString(nl2br);
});

new StoriesView();

import * as hbs from 'handlebars';

export const template = hbs.compile(`
    <div class="backlog-list mdl-grid">
    </div>

    <button id="add-story-button" class="mdl-button mdl-js-button mdl-button--fab mdl-button--colored">
        <i class="material-icons">add</i>
    </button>
`);
export default template;
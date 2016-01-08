import * as hbs from 'handlebars';

export const template = hbs.compile(`
    <div class="mdl-card__title mdl-card--expand backlog-story-title">
        {{#if state.showText}}
            <div class="backlog-story-title-block">
                {{^if state.isEditMode}}
                    <h4>{{nl2br story.text }}</h4>
                {{else}}
                    <div class="mdl-textfield mdl-js-textfield">
                        <textarea
                                id="text"
                                name="text"
                                class="mdl-textfield__input">{{story.text}}</textarea>
                        <label class="mdl-textfield__label" for="text">Story text</label>
                    </div>
                {{/if}}
            </div>
        {{else}}
            <div class="backlog-story-title-block">
                <h5>Требования</h5>
                <ul class="mdl-list">
                    {{#story.requirements}}
                        <li class="mdl-list__item">
                            <span class="mdl-list__item-primary-content">
                                <i class="material-icons">crop_original</i>
                                {{name}}
                            </span>
                            <a class="mdl-list__item-secondary-action"><i class="material-icons">star_border</i></a>
                        </li>
                    {{/story.requirements}}
                    {{#state.isEditMode}}
                        <li class="mdl-list__item">
                            <span class="mdl-list__item-primary-content">
                                <div class="mdl-textfield mdl-js-textfield">
                                    <input name="name" class="mdl-textfield__input" type="text" id="name">
                                    <label class="mdl-textfield__label" for="name">Name...</label>
                                </div>
                            </span>
                            <button class="add-requirement mdl-list__item-secondary-action mdl-button mdl-js-button mdl-button--icon">
                                <i class="material-icons">add</i>
                            </button>
                        </li>
                    {{/state.isEditMode}}
                </ul>
            </div>
        {{/if}}
    </div>

    {{^if state.isEditMode}}
        <div class="mdl-card__actions mdl-card--border">
            <a class="edit mdl-button mdl-button--colored mdl-js-button">
                Редактировать
            </a>
            <a class="requirements mdl-button mdl-button--colored mdl-js-button">
                Требования
            </a>
        </div>
    {{else}}
        <div class="mdl-card__actions mdl-card--border">
            <a class="save mdl-button mdl-button--colored mdl-js-button">
                Сохранить
            </a>
            <a class="cancel mdl-button mdl-button--colored mdl-js-button">
                Отмена
            </a>
        </div>
    {{/if}}

    <div class="mdl-card__menu">
        <button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect">
            <i class="material-icons">{{#if story.completed}}check_box{{else}}check_box_outline_blank{{/if}}</i>
        </button>
    </div>
`);
export default template;

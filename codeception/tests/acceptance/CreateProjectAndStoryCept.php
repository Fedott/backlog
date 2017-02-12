<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('create project and story');

$I->loginAsTestUser();

$I->click('#add-story-button');
$I->fillField('#application > div > div > div > div > div > div > div > div > div.mdl-card__title.mdl-card--expand.backlog-project-name > input[type="text"]', 'TestProject');
$I->click('#application > div > div > div > div > div > div > div > div > div.mdl-card__actions.mdl-card--border > button:nth-child(1)');
$I->waitForText('TestProject');

$I->see('Список историй');
$I->click('Список историй');

$I->click('#add-story-button');
$I->fillField('Title', 'Story title');
$I->fillField('Text', 'Story text');
$I->click('Сохранить');

$I->waitForText('Story title', 1);
$I->see('Story title');
$I->see('Story text');

$I->click('Редактировать');
$I->appendField('Title', ' edited');
$I->appendField('Text', ' edited');
$I->click('Сохранить');

$I->waitForText('Story title edited', 1);
$I->see('Story title edited');
$I->see('Story text edited');

$I->click('Пометить готовой');

$I->dontSee('Story title edited');
$I->dontSee('Story text edited');

<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('create project and story');

$I->loginAsTestUser();

$I->click('#add-story-button');
$I->fillField('Name', 'TestProject');
$I->click('Сохранить');
$I->waitForText('TestProject');

$I->see('Список историй');
$I->click('Список историй');

$I->click('.add-story-button');
$I->fillField('#backlog-story-edit-title', 'Story title');
$I->fillField('#backlog-story-edit-text', 'Story text');
$I->click('Сохранить');

$I->waitForText('Story title', 1);
$I->see('Story title');
$I->see('Story text');

$I->click('Редактировать');
$I->appendField('#backlog-story-edit-title', ' edited');
$I->appendField('#backlog-story-edit-text', ' edited');
$I->click('Сохранить');

$I->waitForText('Story title edited', 1);
$I->see('Story title edited');
$I->see('Story text edited');

$I->click('Пометить готовой');

$I->dontSee('Story title edited');
$I->dontSee('Story text edited');

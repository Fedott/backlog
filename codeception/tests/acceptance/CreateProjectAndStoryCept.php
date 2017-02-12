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

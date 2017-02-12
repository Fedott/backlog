<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('create project and story');

$I->loginAsTestUser();

$I->click('#add-story-button');
$I->fillField('#application > div > div > div > div > div > div > div > div > div.mdl-card__title.mdl-card--expand.backlog-project-name > input[type="text"]', 'TestProject');
$I->click('#application > div > div > div > div > div > div > div > div > div.mdl-card__actions.mdl-card--border > button:nth-child(1)');
$I->waitForText('TestProject');

<?php
use Page\ProjectCard;

$I = new AcceptanceTester($scenario);
$I->wantTo('create project and story, mark story as completed, add requirement');

$I->loginAsTestUser();

$I->click('.add-project-button');
$I->fillField('#backlog-project-edit-name', 'TestProject');
$I->click('Сохранить');
$I->waitForText('TestProject');

$project1Card = new ProjectCard($I, 'TestProject');
$project1Card->showStoryList();

$I->click('.add-story-button');
$I->fillField('#backlog-story-edit-title', 'Story title');
$I->fillField('#backlog-story-edit-text', 'Story text');
$I->click('Сохранить');

$I->waitForText('Story title', 1);
$I->see('Story title');
$I->see('Story text');

$story1Card = new \Page\StoryCard($I, 'Story title');

$story1Card->clickRequirements();
$firstRequirementText = 'First requirement';
$story1Card->fillNewRequirementInput($firstRequirementText);
$story1Card->clickSaveRequirement();
$story1Card->waitRequirement($firstRequirementText);

$secondRequirement = 'Second requirement';
$story1Card->fillNewRequirementInput($secondRequirement);
$story1Card->clickSaveRequirement();
$story1Card->waitRequirement($secondRequirement);

$story1Card->clickEdit();
$story1Card->appendFieldTitle(' edited');
$story1Card->appendFieldText(' edited');
$story1Card->clickSave();

$story1Card->waitTitle('Story title edited', 1);
$story1Card->checkData('Story title edited', 'Story text edited');

$story1Card->clickMarkCompleted();

$story1Card->checkDontSee();

$I->amOnPage('/');

$project1Card->waitSeeCard();
$project1Card->showStoryList();

$story1Card->checkDontSee();

<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('login on test user');
$I->clearLocalStorage();
$I->amOnPage('/');
$I->see('Войти');
$I->click('#application > div > div > div > div > header > div > div:nth-child(3) > button:nth-child(3)');
$I->fillField('#textfield-', 'testUser');
$I->fillField('input[type=password]', 'testPassword');
$I->click('Войти');
$I->waitForText('Привет, testUser');

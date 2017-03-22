<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('login on test user');
$I->clearLocalStorage();

$I->amOnPage('/');

$I->waitForElement('#register-button');
$I->click('#register-button');

$I->see("Регистрация");
$I->fillField('#register-dialog-username', 'testRegistrationUser');
$I->fillField('#register-dialog-password', 'testRegistrationUserPassword');
$I->click('Зарегистрироваться', '.register-dialog');

$I->waitForText('Привет, testRegistrationUser', 1);

<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('see page with header menu');
$I->clearLocalStorage();

$I->amOnPage('/');
$I->see('Backlog');
$I->see('Войти');
$I->see('Зарегистрироваться');

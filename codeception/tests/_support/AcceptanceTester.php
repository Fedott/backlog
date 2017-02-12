<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function clearLocalStorage()
    {
        $this->amOnPage('/');
        $this->executeJS('localStorage.clear();');
    }

    public function loginAsTestUser()
    {
        $I = $this;

        $I->clearLocalStorage();

        $I->amOnPage('/');
        $I->see('Войти');
        $I->click('#login-button');
        $I->fillField('#login-dialog-username', 'testUser');
        $I->fillField('#login-dialog-password', 'testPassword');
        $I->click('Войти', '#login-dialog');
        $I->waitForText('Привет, testUser');
    }
}

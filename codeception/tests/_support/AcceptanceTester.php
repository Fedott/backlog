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
        $this->executeJS('localStorage.clear();');
    }

    public function loginAsTestUser()
    {
        $I = $this;

        $I->amOnPage('/');
        $I->clearLocalStorage();
        $I->amOnPage('/');
        $I->see('Войти');
        $I->click('#application > div > div > div > div > header > div > div:nth-child(3) > button:nth-child(3)');
        $I->fillField('#textfield-', 'testUser');
        $I->fillField('input[type=password]', 'testPassword');
        $I->click('Войти');
        $I->waitForText('Привет, testUser');
    }
}

<?php
namespace Page;

use AcceptanceTester;

class StoryCard
{
    /**
     * @var AcceptanceTester
     */
    protected $tester;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $cardSelector;

    public function __construct(AcceptanceTester $tester, string $storyTitle)
    {
        $this->tester = $tester;

        $this->grabCard($storyTitle);
    }

    protected function grabCard(string $title)
    {
        $this->id = $this->tester->grabAttributeFrom(
            "//*[@class='backlog-story']//div[contains(., '{$title}') and @class='backlog-story-title']/../..",
            'data-story-id'
        );

        $this->cardSelector = "div.backlog-story[data-story-id='{$this->id}']";
    }

    public function clickEdit()
    {
        $this->tester->click('Редактировать', $this->cardSelector);
    }

    public function appendFieldTitle(string $value)
    {
        $this->tester->appendField("{$this->cardSelector} #backlog-story-edit-title", $value);
    }

    public function appendFieldText(string $value)
    {
        $this->tester->appendField("{$this->cardSelector} #backlog-story-edit-text", $value);
    }

    public function clickSave()
    {
        $this->tester->click('Сохранить', $this->cardSelector);
    }

    public function waitTitle(string $title, int $timeout = 1)
    {
        $this->tester->waitForText($title, $timeout, $this->cardSelector);
    }

    public function checkData(string $title, string $text)
    {
        $this->tester->see($title, $this->cardSelector);
        $this->tester->see($text, $this->cardSelector);
    }

    public function clickMarkCompleted()
    {
        $this->tester->click('Пометить готовой', $this->cardSelector);
    }

    public function checkDontSee()
    {
        $this->tester->dontSeeElement($this->cardSelector);
    }
}

<?php
namespace Page;

use AcceptanceTester;

class ProjectCard
{
    /**
     * @var AcceptanceTester
     */
    protected $tester;

    /**
     * @var string
     */
    protected $projectId;

    /**
     * @var string
     */
    protected $projectCardSelector;

    public function __construct(AcceptanceTester $tester, string $projectName)
    {
        $this->tester = $tester;

        $this->grabCard($projectName);
    }

    protected function grabCard(string $projectName)
    {
        $this->projectId = $this->tester->grabAttributeFrom(
            "//*[@class='backlog-project']//div[contains(., '{$projectName}') and @class='backlog-project-name']/../..",
            'data-project-id'
        );

        $this->projectCardSelector = "div.backlog-project[data-project-id='{$this->projectId}']";
    }

    public function showStoryList()
    {
        $this->tester->see('Список историй', $this->projectCardSelector);
        $this->tester->click('Список историй', $this->projectCardSelector);
    }
}

<?php

namespace BacklogBundle\Controller;

use BacklogBundle\Test\WebTestCase;

class RequirementsControllerTest extends WebTestCase
{
    public function testPostRequirementAction()
    {
        $client = static::createClient();

        $story = $this->createStory('Test text story');

        $requirementsUri = "/stories/{$story['id']}/requirements";

        $this->requestJson('GET', $requirementsUri);

        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode(), $this->getClient()->getResponse()->getContent());
        $this->assertEquals('[]', $this->getClient()->getResponse()->getContent());
    }
}

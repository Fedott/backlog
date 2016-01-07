<?php

namespace BacklogBundle\Controller;

use BacklogBundle\Document\Story\Story;
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

    public function testPostReequirement()
    {
        $story = new Story();
        $story->setText('Text');
        $this->getDocumentManager()->persist($story);
        $this->getDocumentManager()->flush();

        $body = [
            'name' => 'Requirement text',
            'isComplete' => false,
        ];
        $this->requestJson('POST', "/stories/{$story->getId()}/requirements", $body);
        $response = $this->getClient()->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertContains('Requirement text', $response->getContent());

        $this->requestJson("GET", "/stories/{$story->getId()}/requirements");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $responseArray = json_decode($response->getContent(), true);
        $this->assertCount(1, $responseArray);

        $this->requestJson("GET", "/stories/{$story->getId()}");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $responseArray = json_decode($response->getContent(), true);
        $this->assertCount(1, $responseArray['requirements']);
        $requirementArray = $responseArray['requirements'][0];
        $this->assertArrayHasKey('isComplete', $requirementArray);
    }
}

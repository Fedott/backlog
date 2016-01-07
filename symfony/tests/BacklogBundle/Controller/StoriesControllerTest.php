<?php

namespace BacklogBundle\Controller;

use BacklogBundle\Document\Story\Story;
use BacklogBundle\Test\WebTestCase;

class StoriesControllerTest extends WebTestCase
{
    public function testGetStoriesAction()
    {
        $this->getClient()->request('GET', '/stories');

        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode(), $this->getClient()->getResponse()->getContent());

        $this->assertEquals('[]', $this->getClient()->getResponse()->getContent());
    }

    public function testPostStoriesAction()
    {
        $body = [
            'text' => 'Text test text',
        ];
        $this->requestJson('POST', '/stories', $body);
        $response = $this->getClient()->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertContains('Text test text', $response->getContent());

        $storyArray = json_decode($response->getContent(), true);

        $this->requestJson("GET", "/stories/{$storyArray['id']}");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }

    public function testPutStoryAction()
    {
        $story = new Story();
        $story->setText('Before edit');

        $this->getDocumentManager()->persist($story);
        $this->getDocumentManager()->flush();

        $body = [
            'text' => 'After edit',
        ];

        $this->requestJson('PUT', "/stories/{$story->getId()}", $body);
        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertContains('After edit', $response->getContent());
        $storyArray = json_decode($response->getContent(), true);
        $this->assertEquals('After edit', $storyArray['text']);

        $this->requestJson("GET", "/stories/{$story->getId()}");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $storyArray = json_decode($response->getContent(), true);
        $this->assertEquals('After edit', $storyArray['text']);
    }
}

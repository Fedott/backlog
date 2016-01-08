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
            'completed' => true,
        ];

        $this->requestJson('PUT', "/stories/{$story->getId()}", $body);
        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertContains('After edit', $response->getContent());
        $storyArray = json_decode($response->getContent(), true);
        $this->assertEquals('After edit', $storyArray['text']);
        $this->assertEquals(true, $storyArray['completed']);

        $this->requestJson("GET", "/stories/{$story->getId()}");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $storyArray = json_decode($response->getContent(), true);
        $this->assertEquals('After edit', $storyArray['text']);
        $this->assertEquals(true, $storyArray['completed']);
    }

    public function testGetStoriesActionWithReadyStories()
    {
        $this->clearMongoDb();

        $story1 = new Story();
        $story1->setText('First');
        $story2 = new Story();
        $story2->setText('Second');
        $story3 = new Story();
        $story3->setText('Story 3');
        $story4 = new Story();
        $story4->setText('Story 4');
        $story5 = new Story();
        $story5->setText('Story 5');

        $this->getDocumentManager()->persist($story1);
        $this->getDocumentManager()->persist($story2);
        $this->getDocumentManager()->persist($story3);
        $this->getDocumentManager()->persist($story4);
        $this->getDocumentManager()->persist($story5);
        $this->getDocumentManager()->flush();

        $this->requestJson("GET", "/stories");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $responseArray = json_decode($response->getContent(), true);
        $this->assertCount(5, $responseArray);


        $this->getDocumentManager()->clear();
        $story3->setCompleted(true);
        $this->getDocumentManager()->persist($story3);
        $this->getDocumentManager()->flush();

        $this->requestJson("GET", "/stories");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertNotContains($story3->getText(), $response->getContent());
        $responseArray = json_decode($response->getContent(), true);
        $this->assertCount(4, $responseArray);


        $this->getDocumentManager()->clear();
        $story1->setCompleted(true);
        $this->getDocumentManager()->persist($story1);
        $this->getDocumentManager()->flush();

        $this->requestJson("GET", "/stories");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertNotContains($story1->getText(), $response->getContent());
        $this->assertNotContains($story3->getText(), $response->getContent());
        $responseArray = json_decode($response->getContent(), true);
        $this->assertCount(3, $responseArray);


        $this->getDocumentManager()->clear();
        $story2->setCompleted(true);
        $story4->setCompleted(true);
        $story5->setCompleted(true);
        $this->getDocumentManager()->persist($story2);
        $this->getDocumentManager()->persist($story4);
        $this->getDocumentManager()->persist($story5);
        $this->getDocumentManager()->flush();
        $this->requestJson("GET", "/stories");

        $response = $this->getClient()->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());
        $this->assertNotContains($story1->getText(), $response->getContent());
        $this->assertNotContains($story2->getText(), $response->getContent());
        $this->assertNotContains($story3->getText(), $response->getContent());
        $this->assertNotContains($story4->getText(), $response->getContent());
        $this->assertNotContains($story5->getText(), $response->getContent());
        $responseArray = json_decode($response->getContent(), true);
        $this->assertCount(0, $responseArray);
    }
}

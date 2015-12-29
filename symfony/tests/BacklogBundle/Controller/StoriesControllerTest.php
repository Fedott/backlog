<?php
namespace BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class StoriesControllerTest extends WebTestCase
{
    public function testGetStoriesAction()
    {
        $client = static::createClient();

        $client->request('GET', '/stories');

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $this->assertEquals('[]', $client->getResponse()->getContent());
    }

    public function testPostStoriesAction()
    {
        $client = static::createClient();

        $body = json_encode([
            'text' => 'Text test text',
        ]);
        $response = $client->request('POST', '/stories', [], [], ['CONTENT_TYPE' => 'application/json', 'CONTENT_LENGTH' => strlen($body)], $body);

        $this->assertEquals(201, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertContains('Text test text', $client->getResponse()->getContent());
    }
}

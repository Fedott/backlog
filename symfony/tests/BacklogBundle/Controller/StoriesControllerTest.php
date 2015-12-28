<?php
namespace BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StoriesControllerTest extends WebTestCase
{
    public function testGetStoriesMethod()
    {
        $client = static::createClient();

        $client->request('GET', '/stories');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals('[]', $client->getResponse()->getContent());
    }
}

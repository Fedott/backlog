<?php
namespace BacklogBundle\Test;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class WebTestCase extends BaseWebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->clearMongoDb();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = static::createClient();
        }

        return $this->client;
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->client->getContainer()->get('doctrine_mongodb.odm.document_manager');
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|string $body
     * @return Crawler
     */
    protected function requestJson($method, $uri, $body = null)
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        return $this->getClient()->request($method, $uri, [], [], ['CONTENT_TYPE' => 'application/json'], $body);
    }

    protected function clearMongoDb()
    {
        $this->getDocumentManager()->getSchemaManager()->dropDatabases();
    }

    /**
     * @param string $text
     * @return array
     */
    protected function createStory($text)
    {
        $this->requestJson('POST', '/stories', ['text' => $text]);

        $this->assertEquals(201, $this->getClient()->getResponse()->getStatusCode());
        $this->assertJson($this->getClient()->getResponse()->getContent());

        return json_decode($this->getClient()->getResponse()->getContent(), true);
    }
}

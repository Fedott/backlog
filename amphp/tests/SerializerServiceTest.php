<?php
namespace Tests\Fedot\Backlog;

use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Request;
use Fedot\Backlog\SerializerService;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testParseRequest()
    {
        $requestJsonString = <<<JSON
{
    "id": 234,
    "type": "test",
    "payload": {
        "field1": 564,
        "field3": "dfsdf",
        "extraField": "55trt"
    }
}
JSON;

        $serializer = new Serializer(
            [new ObjectNormalizer()],
            [new JsonDecode()]
        );

        $serializerService = new SerializerService($serializer);
        $serializerService->addPayloadType('test', TestPayload::class);

        $actualRequest = $serializerService->parseRequest($requestJsonString);
        $actualPayload = $actualRequest->payload;

        $this->assertInstanceOf(Request::class, $actualRequest);
        $this->assertEquals('test', $actualRequest->type);
        $this->assertEquals('234', $actualRequest->id);

        $this->assertInstanceOf(TestPayload::class, $actualPayload);
        $this->assertEquals(564, $actualPayload->field1);
        $this->assertEquals("dfsdf", $actualPayload->field3);
    }
}

class TestPayload implements PayloadInterface
{
    /**
     * @var int
     */
    public $field1;

    /**
     * @var string
     */
    public $field3;
}

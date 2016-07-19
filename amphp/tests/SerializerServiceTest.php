<?php
namespace Tests\Fedot\Backlog;

use Fedot\Backlog\Request\Request;
use Fedot\Backlog\SerializerService;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tests\Fedot\Backlog\Stubs\TestPayload;
use Tests\Fedot\Backlog\Stubs\TestProcessor;

class SerializerServiceTest extends BaseTestCase
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
        $serializerService->addAllPayloadTypesFromProcessors([
            new TestProcessor(),
        ]);

        $actualRequest = $serializerService->parseRequest($requestJsonString);
        $actualPayload = $serializerService->parsePayload($actualRequest);

        $this->assertInstanceOf(Request::class, $actualRequest);
        $this->assertEquals('test', $actualRequest->type);
        $this->assertEquals('234', $actualRequest->id);

        $this->assertInstanceOf(TestPayload::class, $actualPayload);
        $this->assertEquals(564, $actualPayload->field1);
        $this->assertEquals("dfsdf", $actualPayload->field3);
    }
}

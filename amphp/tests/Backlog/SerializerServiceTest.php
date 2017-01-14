<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog;

use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocket\Request;
use RuntimeException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tests\Fedot\Backlog\Stubs\NestedObject;
use Tests\Fedot\Backlog\Stubs\TestPayload;
use Tests\Fedot\Backlog\Stubs\TestAction;

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
        "extraField": "55trt",
        "nestedObject": {
            "field1": "testValue"
        }
    }
}
JSON;

        $extractor = new PropertyInfoExtractor(array(), array(new PhpDocExtractor()));
        $serializer = new Serializer(
            [new ObjectNormalizer(null, null, null, $extractor)],
            [new JsonDecode()]
        );

        $serializerService = new SerializerService($serializer);
        $serializerService->addAllPayloadTypesFromActions([
            new TestAction(),
        ]);

        $actualRequest = $serializer->deserialize($requestJsonString, Request::class, 'json');
        $actualPayload = $serializerService->parsePayload($actualRequest);

        $this->assertInstanceOf(Request::class, $actualRequest);
        $this->assertEquals('test', $actualRequest->getType());
        $this->assertEquals('234', $actualRequest->getId());

        $this->assertInstanceOf(TestPayload::class, $actualPayload);
        $this->assertEquals(564, $actualPayload->field1);
        $this->assertEquals("dfsdf", $actualPayload->field3);

        $this->assertInstanceOf(NestedObject::class, $actualPayload->nestedObject);
        $this->assertEquals('testValue', $actualPayload->nestedObject->field1);
    }
    public function testParseRequestNotFoundPayload()
    {
        $requestJsonString = <<<JSON
{
    "id": 234,
    "type": "test-t",
    "payload": {
        "field1": 564,
        "field3": "dfsdf",
        "extraField": "55trt",
        "nestedObject": {
            "field1": "testValue"
        }
    }
}
JSON;

        $extractor = new PropertyInfoExtractor(array(), array(new PhpDocExtractor()));
        $serializer = new Serializer(
            [new ObjectNormalizer(null, null, null, $extractor)],
            [new JsonDecode()]
        );

        $serializerService = new SerializerService($serializer);
        $serializerService->addAllPayloadTypesFromActions([
            new TestAction(),
        ]);

        $actualRequest = $serializer->deserialize($requestJsonString, Request::class, 'json');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Not found payload for request type: test-t");
        $serializerService->parsePayload($actualRequest);
    }
}

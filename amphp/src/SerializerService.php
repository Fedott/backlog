<?php
namespace Fedot\Backlog;

use Symfony\Component\Serializer\Serializer;

class SerializerService
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $payloadTypes = [];

    /**
     * SerializerService constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $type
     * @param string $class
     */
    public function addPayloadType(string $type, string $class)
    {
        $this->payloadTypes[$type] = $class;
    }

    /**
     * @param array $types
     */
    public function addPayloadTypes(array $types)
    {
        foreach ($types as $type => $class) {
            $this->addPayloadType($type, $class);
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function serializeRequest(Request $request): string
    {
        return '';
    }

    public function parsePayload($json, $type): PayloadInterface
    {

    }

    /**
     * @param string $requestJson
     *
     * @return Request
     */
    public function parseRequest(string $requestJson): Request
    {
        $requestJson = json_decode($requestJson, true);

        $type = $requestJson['type'];

        if (!array_key_exists($type, $this->payloadTypes)) {
            throw new \RuntimeException("Not found payload type: {$type}");
        }

        $payloadTypeClass = $this->payloadTypes[$type];

        $payload = $this->serializer->deserialize(json_encode($requestJson['payload']), $payloadTypeClass, 'json');

        $request = new Request(
            $requestJson['id'],
            $type,
            $payload
        );

        return $request;
    }
}

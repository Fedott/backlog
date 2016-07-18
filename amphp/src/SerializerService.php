<?php
namespace Fedot\Backlog;

use Fedot\Backlog\Request\Request;
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

    /**
     * @param Request $request
     * @param string $type
     *
     * @return PayloadInterface
     */
    public function parsePayload(Request $request): PayloadInterface
    {
        $type = $request->type;

        if (!array_key_exists($type, $this->payloadTypes)) {
            throw new \RuntimeException("Not found payload type: {$type}");
        }

        $payloadTypeClass = $this->payloadTypes[$type];

        $payload = $this->serializer->denormalize($request->payload, $payloadTypeClass);

        return $payload;
    }

    /**
     * @param string $requestJson
     *
     * @return Request
     */
    public function parseRequest(string $requestJson): Request
    {
        /** @var Request $request */
        $request = $this->serializer->deserialize($requestJson, Request::class, 'json');

        $request->payload = $this->parsePayload($request, $request->type);

        return $request;
    }
}

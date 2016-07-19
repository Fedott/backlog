<?php
namespace Fedot\Backlog;

use Fedot\Backlog\Request\Processor\ProcessorInterface;
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
    protected function addPayloadType(string $type, string $class)
    {
        $this->payloadTypes[$type] = $class;
    }

    /**
     * @param ProcessorInterface $processor
     */
    public function addPayloadTypeFromProcessor(ProcessorInterface $processor)
    {
        $this->addPayloadType($processor->getSupportedType(), $processor->getExpectedRequestPayload());
    }

    /**
     * @param ProcessorInterface[] $processors
     */
    public function addAllPayloadTypesFromProcessors(array $processors)
    {
        foreach ($processors as $processor) {
            $this->addPayloadTypeFromProcessor($processor);
        }
    }

    /**
     * @param Request $request
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

        return $request;
    }
}

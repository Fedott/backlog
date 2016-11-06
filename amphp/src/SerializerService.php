<?php declare(strict_types=1);
namespace Fedot\Backlog;

use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\RequestInterface;
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

    public function parsePayload(RequestInterface $request): PayloadInterface
    {
        $type = $request->getType();

        if (!array_key_exists($type, $this->payloadTypes)) {
            throw new \RuntimeException("Not found payload for request type: {$type}");
        }

        $payloadTypeClass = $this->payloadTypes[$type];

        $payload = $this->serializer->denormalize($request->getPayload(), $payloadTypeClass);

        return $payload;
    }
}

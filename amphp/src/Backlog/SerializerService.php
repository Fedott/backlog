<?php declare(strict_types=1);
namespace Fedot\Backlog;

use Fedot\Backlog\Action\ActionInterface;
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
     * @param ActionInterface $action
     */
    public function addPayloadTypeFromAction(ActionInterface $action)
    {
        $this->addPayloadType($action->getSupportedType(), $action->getExpectedRequestPayload());
    }

    /**
     * @param ActionInterface[] $actions
     */
    public function addAllPayloadTypesFromActions(array $actions)
    {
        foreach ($actions as $action) {
            $this->addPayloadTypeFromAction($action);
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

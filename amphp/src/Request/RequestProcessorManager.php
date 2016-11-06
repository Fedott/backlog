<?php declare(strict_types=1);
namespace Fedot\Backlog\Request;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class RequestProcessorManager
{
    /**
     * @var ProcessorInterface[]
     */
    protected $processors = [];

    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @param ProcessorInterface[] $processors
     */
    public function addProcessors(array $processors)
    {
        array_map(function (ProcessorInterface $processor) {
            $this->addProcessor($processor);
        }, $processors);
    }

    public function process(RequestInterface $request, ResponseInterface $response): Promise
    {
        foreach ($this->processors as $processor) {
            if ($processor->supportsRequest($request)) {
                return $processor->process($request, $response);
            }
        }

        return new Success($response);
    }
}

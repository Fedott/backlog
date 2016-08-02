<?php declare(strict_types=1);
namespace Fedot\Backlog\Request;

use Fedot\Backlog\Request\Processor\ProcessorInterface;

class RequestProcessorManager
{
    /**
     * @var ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * @param ProcessorInterface $processor
     */
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

    /**
     * @param Request $request
     */
    public function process(Request $request)
    {
        foreach ($this->processors as $processor) {
            if ($processor->supportsRequest($request)) {
                \Amp\immediately(function () use ($processor, $request) {
                    yield from $processor->process($request);
                });

                return;
            }
        }
    }
}

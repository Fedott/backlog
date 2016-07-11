<?php
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
            $this->processors[] = $processor;
        }, $processors);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function process(Request $request): bool
    {
        foreach ($this->processors as $processor) {
            if ($processor->supportsRequest($request)) {
                if ($processor->process($request)) {
                    return true;
                }
            }
        }

        return false;
    }
}

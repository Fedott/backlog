<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Request;

interface ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool;

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function process(Request $request): bool;
}

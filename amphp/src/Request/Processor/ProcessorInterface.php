<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Request;

interface ProcessorInterface
{
    public function process(Request $request): bool;

    public function supportsRequest(Request $request): bool;
}

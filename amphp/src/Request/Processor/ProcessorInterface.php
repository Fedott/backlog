<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Fedot\Backlog\Request\Request;
use Generator;

interface ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool;

    /**
     * @return string
     */
    public function getSupportedType(): string;

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string;

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator;
}

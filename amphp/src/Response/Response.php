<?php declare(strict_types=1);
namespace Fedot\Backlog\Response;

use Fedot\Backlog\PayloadInterface;

class Response
{
    /**
     * @var int
     */
    public $requestId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var PayloadInterface
     */
    public $payload;
}

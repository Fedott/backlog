<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

class ErrorPayload
{
    public $message;

    public function __construct(string $message = '')
    {
        $this->message = $message;
    }
}

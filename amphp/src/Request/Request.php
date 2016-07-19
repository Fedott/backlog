<?php
namespace Fedot\Backlog\Request;

use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Response\ResponseSender;

class Request
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var PayloadInterface
     */
    public $payload;

    /**
     * @var int
     */
    protected $clientId;

    /**
     * @var ResponseSender
     */
    protected $responseSender;

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     *
     * @return $this
     */
    public function setClientId(int $clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return ResponseSender
     */
    public function getResponseSender(): ResponseSender
    {
        return $this->responseSender;
    }

    /**
     * @param ResponseSender $responseSender
     *
     * @return $this
     */
    public function setResponseSender(ResponseSender $responseSender)
    {
        $this->responseSender = $responseSender;

        return $this;
    }
}

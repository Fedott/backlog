<?php
namespace Fedot\Backlog;

class SerializerService
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public function serializeRequest(Request $request): string
    {
        return '';
    }

    public function unserializePayload($json, $type): Payload
    {

    }

    /**
     * @param string $requestJson
     *
     * @return Request
     */
    public function unserializeRequest(string $requestJson): Request
    {

    }
}

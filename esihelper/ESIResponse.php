<?php

namespace ESIHelper;

class ESIResponse
{
    public $status_code;

    public $is_cached;

    public $response_text;

    public $headers = [];

    /**
     * ESIResponse constructor.
     *
     * @param $status_code
     * @param $is_cached
     * @param $response_text
     * @param array $headers
     */
    public function __construct($status_code, $is_cached, $response_text, array $headers)
    {
        $this->status_code = $status_code;
        $this->is_cached = $is_cached;
        $this->response_text = $response_text;
        $this->headers = $headers;
    }

    public function isCached()
    {
        return $this->is_cached;
    }
}
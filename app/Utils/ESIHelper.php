<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\UriTemplate;

class ESIHelper
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://esi.evetech.net']);
    }

    public function execute($method, $url, $params = [], $query = [], $body = [])
    {
        $method = strtolower($method);
        $options = [];
        $options['query'] = $query;
        $options['headers']['User-Agent'] = 'EvEHelper/v1.0 (admin@eve-info.net)';
        if ($method == 'post') {
            $options['body'] = $body;
        }
        $uri_template = new UriTemplate();
        $url = $uri_template->expand($url,$params);
        $result = $this->client->$method($url, $options);
        return $result->getBody()->getContents();
    }
}
<?php

namespace ESIHelper;

use Carbon\Carbon;
use ESIHelper\Cache\LaravelRedisCache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\UriTemplate;

class ESIHelper
{
    public static $client = null;

    public static $cache_system;

    public function __construct()
    {
        if (self::$client == null) {
            self::$client = new Client(['base_uri' => 'https://esi.evetech.net']);
        }
        if (self::$cache_system == null) {
            self::$cache_system = new LaravelRedisCache();
        }
    }

    public function invoke($method, $url, $params = [], $query = [], $body = '')
    {
        //todo: replace these fucking code in someday
        $method = strtolower($method);
        $options = [];
        $options['query'] = $query;
        $options['headers']['User-Agent'] = 'EvEHelper/v1.0 (admin@eve-info.net)';
        if ($method == 'post') {
            $options['body'] = $body;
        }
        $uri_template = new UriTemplate();
        $url = $uri_template->expand($url, $params);
        $etag = null;
        if ($method == 'get') {
            if (self::$cache_system->has($url)) {
                $etag = self::$cache_system->get($url);
                $cache_data = self::$cache_system->get($etag);
                $options['headers']['If-None-Match'] = $etag;
            }
        }
        $esi_response = null;
        try {
            $response = self::$client->$method($url, $options);
            if ($response->getStatusCode() == 304) {
                $esi_response = new ESIResponse($response->getStatusCode(), true, $cache_data, $response->getHeaders());
            } elseif ($response->getStatusCode() == 200) {
                $result = $response->getBody()->getContents();
                if ($method == 'get') {
                    if ($etag != null) {
                        self::$cache_system->forget($etag);
                    }
                    $etag = $response->getHeader('etag')[0];
                    $expire_date = new Carbon($response->getHeader('expires')[0]);
                    //$minutes = $expire_date->diffInMinutes(Carbon::now());
                    $minutes = 60 * 24 * 60;
                    self::$cache_system->set($etag, $result, $minutes);
                    self::$cache_system->set($url, $etag, $minutes);
                }

                $esi_response = new ESIResponse($response->getStatusCode(), false, $result, $response->getHeaders());
            }
        } catch (RequestException $e) {
            $esi_response = new ESIResponse($e->getCode(), false, $e->getResponse()->getBody()->getContents(), $e->getResponse()->getHeaders());
        }
        return $esi_response;
    }
}
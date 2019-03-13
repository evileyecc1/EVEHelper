<?php

namespace ESIHelper\Cache;

use Illuminate\Support\Facades\Cache;

class LaravelRedisCache implements ICache
{
    public $prefix = 'esihelper:';

    public function get($key, $default = '')
    {
        $key = sha1($key);

        return Cache::get($this->prefix.$key, $default);
    }

    public function set($key, $value, $ttl = 60)
    {
        $key = sha1($key);

        Cache::put($this->prefix.$key, $value, $ttl);
    }

    public function has($key)
    {
        $key = sha1($key);

        return Cache::has($this->prefix.$key);
    }

    public function forget($key)
    {
        $key = sha1($key);

        return Cache::forget($this->prefix.$key);
    }
}
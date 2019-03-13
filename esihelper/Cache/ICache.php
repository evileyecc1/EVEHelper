<?php
namespace ESIHelper\Cache;

interface ICache{

    public function get($key,$default = '');

    public function set($key,$value,$ttl = 60);

    public function has($key);

    public function forget($key);
}
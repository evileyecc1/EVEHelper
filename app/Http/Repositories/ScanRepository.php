<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\Cache;
use App\Exceptions\CacheKeyExistsException;

class ScanRepository
{
    private $cache_prefix = 'scan_result';

    private function generateRandomLetters($length)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }

        return $random;
    }

    public function create($scan_result, $expire_time = 604800)
    {
        $uuid = $this->generateRandomLetters(12);
        if (Cache::has($this->cache_prefix.$uuid)) {
            throw new CacheKeyExistsException();
        }

        $result = Cache::add($this->cache_prefix.$uuid, $scan_result, $expire_time / 60);
        return $uuid;
    }

    public function getById($id)
    {
        if (Cache::has($this->cache_prefix.$id)) {
            return Cache::get($this->cache_prefix. $id);
        }

        return false;
    }

    public function update($id, $scan_result, $expire_time = 604800)
    {
        Cache::put($id, $scan_result, $expire_time / 60);
    }
}

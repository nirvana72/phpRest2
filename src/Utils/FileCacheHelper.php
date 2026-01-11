<?php
declare(strict_types=1);

namespace PhpRest2\Utils;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class FileCacheHelper
{
    private FilesystemAdapter $cache;

    public function __construct(string $directory) {
        $this->cache = new FilesystemAdapter(directory: $directory);
    }

    public function get(string $key): mixed {
        $cacheItem = $this->cache->getItem($key);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }
        return null;
    }

    public function set(string $key, mixed $val, int $expires = 0): void {
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($val);
        if ($expires > 0) {
            $cacheItem->expiresAfter($expires);
        }
        $this->cache->save($cacheItem);
    }
}

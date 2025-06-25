<?php
namespace Horus\Cache\Contracts;

interface CacheDriverInterface {
    public function get(string $key, $default = null);
    public function set(string $key, $value, int $ttlInSeconds = 3600): bool;
    public function has(string $key): bool;
    public function forget(string $key): bool;
    public function remember(string $key, int $ttlInSeconds, callable $callback);
    public function increment(string $key, int $value = 1, int $ttlInSeconds = 3600): int|false;
}

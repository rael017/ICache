<?php
namespace Horus\Cache\Drivers;

use Horus\Cache\Contracts\CacheDriverInterface;
use Predis\Client;

class RedisCache implements CacheDriverInterface
{
    private Client $redis;
    public function __construct(array $config) { $this->redis = new Client($config); }
    public function get(string $key, $default = null) { $value = $this->redis->get($key); return $value ? unserialize($value) : $default; }
    public function set(string $key, $value, int $ttl = 3600): bool { return (bool) $this->redis->setex($key, $ttl, serialize($value)); }
    public function has(string $key): bool { return (bool) $this->redis->exists($key); }
    public function forget(string $key): bool { return (bool) $this->redis->del($key); }
    public function remember(string $key, int $ttl, callable $callback) { 
        if ($this->has($key)) { 
            return $this->get($key); 
        } 
        $value = $callback(); $this->set($key, $value, $ttl); return $value; 
    }

    public function increment(string $key, int $value = 1, int $ttl = 3600): int|false
    {
        if (!$this->redis->exists($key)) {
            $this->redis->setex($key, $ttl, 0);
        }
        return $this->redis->incrby($key, $value);
    }
}


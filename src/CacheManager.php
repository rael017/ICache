<?php
namespace Horus\ICache;
use Horus\ICache\Contracts\CacheDriverInterface;

class CacheManager
{
    private array $drivers = [];
    public function __construct(private array $config) {}
    public function driver($name = null): CacheDriverInterface
    {
        $name = $name ?: $this->getDefaultDriver();
        if (isset($this->drivers[$name])) return $this->drivers[$name];
        
        $method = 'create' . ucfirst($name) . 'Driver';
        if (!method_exists($this, $method)) throw new \InvalidArgumentException("Driver [{$name}] não suportado.");
        
        return $this->drivers[$name] = $this->$method();
    }
    protected function createRedisDriver(): CacheDriverInterface { return new Drivers\RedisCache($this->config['stores']['redis']); }
    protected function createFileDriver(): CacheDriverInterface { return new Drivers\FileCache($this->config['stores']['file']); }
    protected function createSwooleDriver(): CacheDriverInterface { return new Drivers\SwooleTableCache([]); }
    public function getDefaultDriver(): string { return $this->config['default']; }
}
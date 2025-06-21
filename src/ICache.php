<?php
namespace Horus\ICache;

class Cache
{
    private static ?CacheManager $manager = null;
    public static function init(array $config): void { self::$manager = new CacheManager($config); }
    public static function store(string $driver) { return self::$manager->driver($driver); }
    public static function __callStatic(string $method, array $args) { return self::$manager->driver()->$method(...$args); }
}

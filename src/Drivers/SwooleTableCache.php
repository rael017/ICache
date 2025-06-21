<?php
namespace Horus\ICache\Drivers;
use Horus\ICache\Contracts\CacheDriverInterface;
use Swoole\Table;

class SwooleTableCache implements CacheDriverInterface {
    private static ?Table $table = null;
    public static function initTable(int $size = 4096): void {
        if (self::$table === null && extension_loaded('swoole')) {
            self::$table = new Table($size);
            self::$table->column('value', Table::TYPE_STRING, 8192);
            self::$table->create();
        }
    }
    public function get(string $key, $default = null) { $value = self::$table?->get($key); return $value ? unserialize($value['value']) : $default; }
    public function set(string $key, $value, int $ttl = 3600): bool { return self::$table?->set($key, ['value' => serialize($value)]) ?? false; }
    public function has(string $key): bool { return self::$table?->exists($key) ?? false; }
    public function forget(string $key): bool { return self::$table?->del($key) ?? false; }
    public function remember(string $key, int $ttl, callable $callback) { if ($this->has($key)) { return $this->get($key); } $value = $callback(); $this->set($key, $value, $ttl); return $value; }
    public function increment(string $key, int $value = 1, int $ttl = 3600): int|false
    {
        if (!self::$table) return false;
        
        // Swoole Table incr é atómico e seguro para concorrência
        return self::$table->incr($key, 'value', $value);
    }
}
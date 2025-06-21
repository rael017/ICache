<?php
namespace Horus\ICache\Drivers;
use Horus\ICache\Contracts\CacheDriverInterface;

class FileCache implements CacheDriverInterface {
    private string $path;
    public function __construct(array $config) { $this->path = $config['path']; if (!is_dir($this->path)) { mkdir($this->path, 0755, true); } }
    private function getFilePath(string $key): string { return $this->path . '/' . sha1($key); }

    public function get(string $key, $default = null) {
        if (!$this->has($key)) return $default;
        $content = unserialize(file_get_contents($this->getFilePath($key)));
        return $content['data'];
    }
    public function set(string $key, $value, int $ttl = 3600): bool {
        $data = serialize(['expires' => time() + $ttl, 'data' => $value]);
        return (bool) file_put_contents($this->getFilePath($key), $data, LOCK_EX);
    }
    public function has(string $key): bool {
        $path = $this->getFilePath($key);
        if (!file_exists($path)) return false;
        $content = unserialize(file_get_contents($path));
        if (time() >= $content['expires']) { $this->forget($key); return false; }
        return true;
    }
    public function forget(string $key): bool { return file_exists($this->getFilePath($key)) ? unlink($this->getFilePath($key)) : false; }
    public function remember(string $key, int $ttl, callable $callback) {
        if ($this->has($key)) return $this->get($key);
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }


    public function increment(string $key, int $value = 1, int $ttl = 3600): int|false
    {
        $filePath = $this->getFilePath($key);
        $file = fopen($filePath, 'c+');
        if (flock($file, LOCK_EX)) {
            $currentValue = (int) $this->get($key, 0);
            $newValue = $currentValue + $value;
            $this->set($key, $newValue, $ttl);
            flock($file, LOCK_UN);
            fclose($file);
            return $newValue;
        }
        fclose($file);
        return false;
    }
}


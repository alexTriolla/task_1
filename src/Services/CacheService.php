<?php

class CacheService
{
    private $cacheFile;
    private $cacheDuration;


    public function __construct($cacheFile = 'src/Storage/weather_cache.json', $cacheDuration = 600)
    {
        $this->cacheFile = $cacheFile;
        $this->cacheDuration = $cacheDuration;

        // Ensure the directory exists
        $this->ensureStorageDirectory();
    }

    private function ensureStorageDirectory()
    {
        $directory = dirname($this->cacheFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function getCachedData($key)
    {
        if (file_exists($this->cacheFile)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true);
            if (isset($cache[$key]) && (time() - $cache[$key]['timestamp'] < $this->cacheDuration)) {
                return $cache[$key]['data'];
            }
        }
        return null;
    }

    public function cacheData($key, $data)
    {
        $cache = file_exists($this->cacheFile) ? json_decode(file_get_contents($this->cacheFile), true) : [];
        $cache[$key] = [
            'timestamp' => time(),
            'data' => $data
        ];
        file_put_contents($this->cacheFile, json_encode($cache));
    }
}

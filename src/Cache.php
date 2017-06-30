<?php

namespace Zeus;

class Cache extends Common\Singleton
{

    const DIR = './cache';

    /**
     *
     * @var \Doctrine\Common\Cache\Cache
     */
    private static $cache = null;

    private function __construct()
    {
        $zConf = Configuration::getInstance();
        switch ($zConf->getCache()) {
            case 'apc':
                self::$cache = new \Doctrine\Common\Cache\ApcuCache;
                break;
            case 'couchbase':
                self::$cache = new \Doctrine\Common\Cache\CouchbaseCache;
                break;
            case 'file':
                self::$cache = new \Doctrine\Common\Cache\FilesystemCache(self::DIR);
                break;
            case 'mem':
                self::$cache = new \Doctrine\Common\Cache\MemcacheCache;
                break;
            case 'mongodb':
                throw new \Exception('MongoDB not implemented yet.');
            case 'phpfile':
                self::$cache = new \Doctrine\Common\Cache\PhpFileCache(self::DIR);
                break;
            case 'redis':
                self::$cache = new \Doctrine\Common\Cache\RedisCache;
                break;
            case 'riak':
                throw new \Exception('Riak not implmenented yet.');
            case 'wincache':
                self::$cache = new \Doctrine\Common\Cache\WinCacheCache;
                break;
            case 'xcache':
                self::$cache = new \Doctrine\Common\Cache\XcacheCache;
                break;
            case 'zend':
                self::$cache = new \Doctrine\Common\Cache\ZendDataCache;
                break;
            case 'none':
            default:
                self::$cache = new Cache\Dummy;
                break;
        }
    }

    public function fetch(string $id)
    {
        return self::$cache->fetch($id);
    }

    public function contais(string $id)
    {
        return self::$cache->contains($id);
    }

    public function save(string $id, $data, Cache\CacheTime $lifeTime = Cache\CacheTime::ONE_HOUR)
    {
        return self::$cache->save($id, $data, $lifeTime);
    }

    public function delete(string $id)
    {
        return self::$cache->delete($id);
    }

    public function getStats()
    {
        return self::$cache->getStats();
    }

    public static function setCache($data)
    {
        $cache = self::getInstance();
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $cache->save($uri, $data);
    }

    public static function getCache()
    {
        $cache = self::getInstance();
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        if ($cache->contais($uri)) {
            print_r($cache->fetch($uri));
            exit;
        }
    }

}

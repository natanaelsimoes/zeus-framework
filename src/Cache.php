<?php

namespace Zeus;

class Cache
{

    private static $cache = null;
    private static $instance;
    public $doCache = false;

    /**
     * @return Cache
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    private function __construct()
    {
        $zConf = Configuration::getInstance();
        switch ($zConf->getCache()) {
            case 'apc': self::$cache = new Cache\APC;
                break;
            case 'eaccelerator': self::$cache = new Cache\EAccelerator;
                break;
            case 'xcache': self::$cache = new Cache\XCache;
                break;
            case 'file': self::$cache = new Cache\File;
                break;
            case 'none':
            default:
                self::$cache = new Cache\Dummy;
                break;
        }
    }

    public function __clone()
    {
        throw new Exception('Clone is not allowed.');
    }

    public function setDirectory($dir)
    {
        if (self::$cache instanceof Cache\File) {
            self::$cache->setDirectory($dir);
        }
    }

    public function fetch($var)
    {
        return self::$cache->fetch($var);
    }

    public function store($var, $value, $time = Cache\CacheTime::ONE_HOUR)
    {
        return self::$cache->store($var, $value, $time);
    }

    public function delete($var)
    {
        return self::$cache->delete($var);
    }

    public function clear()
    {
        return self::$cache->clear();
    }

    public static function setCache($data)
    {
        $cache = self::getInstance();
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $cache->store($uri, $data, Cache\CacheTime::ONE_HOUR);
    }

    public static function getCache()
    {
        $cache = self::getInstance();
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $data = $cache->fetch($uri);
        if ($data !== false) {
            print_r($data);
            exit;
        }
    }

}

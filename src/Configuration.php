<?php

namespace Zeus;

class Configuration
{

    const PATH = './zeus.json';
    const NOT_FOUND = 'Zeus configuration file not found.';
    const MIS_ROUTE = 'Missing routes configuration.';

    private $file;
    private static $instance;

    private function __construct()
    {
        if (!file_exists(self::PATH)) {
            throw new \Exception(self::NOT_FOUND);
        }
        $this->file = json_decode(file_get_contents(self::PATH));
        if (!isset($this->file->routes)) {
            throw new \Exception(self::MIS_ROUTE);
        }
    }

    public function __clone()
    {
        throw new \Exception('Cannot clone a singleton class');
    }

    /**
     * @return Configuration
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    public function getInitialDirectory()
    {
        return './' . $this->file->routes->initialDirectory;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getDatabase()
    {
        return (isset($this->file->database)) ? $this->file->database : null;
    }

    public function inDevelopment()
    {
        return $this->file->development;
    }

    public function getIndex()
    {
        return $this->file->routes->index;
    }

    public function getCache()
    {
        if (isset($this->file->cache)) {
            if (strpos('/', $this->file->cache) !== false) {
                return explode('/', $this->file->cache)[0];
            }
            return $this->file->cache;
        }
    }

    public function getCacheParam()
    {
        if (isset($this->file->cache)) {
            if (strpos('/', $this->file->cache) !== false) {
                return explode('/', $this->file->cache)[1];
            }
            return null;
        }
    }

}

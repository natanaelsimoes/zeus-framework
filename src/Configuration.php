<?php

namespace Zeus;

/**
 * Zeus Configuration class access the zeus.json file to get its parameters
 */
class Configuration extends Singleton
{

    const PATH = './zeus.json';
    const NOT_FOUND = 'Zeus configuration file not found.';
    const MIS_ROUTE = 'Missing routes configuration.';

    private $file;

    /**
     * Laod the zeus.json file into this class
     * @throws \Exception If zeus.file do not exists
     */
    protected function __construct()
    {
        if (!file_exists(self::PATH)) {
            throw new \Exception(self::NOT_FOUND);
        }
        $this->file = json_decode(file_get_contents(self::PATH));
        if (!isset($this->file->routes)) {
            throw new \Exception(self::MIS_ROUTE);
        }
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

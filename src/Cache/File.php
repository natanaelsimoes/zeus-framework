<?php

namespace Zeus\Cache;

class File implements Proxy\CacheType
{

    private static $directory = './cache/';

    public function __construct()
    {
        if (!file_exists(self::$directory)) {
            mkdir(self::$directory);
        }
    }

    public function setDirectory($dir)
    {
        self::$directory = $dir;
    }

    public function fetch($var)
    {
        $file = $this->getFile($var);
        $data = $this->getFileData($file);
        if (!$data) {
            unlink($file);
            return false;
        }
        if (time() > $data[0]) {
            unlink($file);
            return false;
        }
        return $data[1];
    }

    public function store($var, $value, $time = CacheTime::ONE_HOUR)
    {
        $fp = fopen($this->getFile($var), 'w+');
        if (!$fp) {
            throw new \Exception('Could not write to cache');
        }
        flock($fp, LOCK_EX); // exclusive lock
        $data = serialize(array(time() + $time, $value));
        if (fwrite($fp, $data) === false) {
            throw new \Exception('Could not write to cache');
        }
        fclose($fp);
        return true;
    }

    public function delete($var)
    {
        $filename = $this->getFile($var);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }

    public function clear()
    {
        $files = glob(self::$directory . '*.*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    private function getFile($var)
    {
        return self::$directory . md5($var) . '.dat';
    }

    private function getFileData($file)
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = fopen($file, 'r');
        if (!$fp) {
            return false;
        }
        flock($fp, LOCK_SH);
        $data = file_get_contents($file);
        fclose($fp);
        return @unserialize($data);
    }

}

<?php

namespace Zeus\Cache;

class Dummy implements Proxy\CacheType
{

    public function fetch($var)
    {
        return false;
    }

    public function store($var, $value, $time = CacheTime::ONE_HOUR)
    {
        return false;
    }

    public function delete($var)
    {
        return false;
    }

    public function clear()
    {
        return false;
    }

}

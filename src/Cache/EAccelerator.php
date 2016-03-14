<?php

namespace Zeus\Cache;

class EAccelerator implements Proxy\CacheType
{

    public function fetch($var)
    {
        $data = eaccelerator_get($var);
        return is_null($data) ? false : $data;
    }

    public function store($var, $value, $time = CacheTime::ONE_HOUR)
    {
        return eaccelerator_put($var, $value, $time);
    }

    public function delete($var)
    {
        return eaccelerator_rm($var);
    }

    public function clear()
    {
        return eaccelerator_clear();
    }

}

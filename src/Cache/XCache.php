<?php

namespace Zeus\Cache;

class XCache implements Proxy\CacheType
{

    public function fetch($var)
    {
        return ( $VarValue = xcache_get($var) ) ? $VarValue : false;
    }

    public function store($var, $value, $time = CacheTime::ONE_HOUR)
    {
        return xcache_set($var, $value, $time);
    }

    public function delete($var)
    {
        return xcache_unset($var);
    }

    public function clear()
    {
        for ($i = 0, $j = xcache_count(XC_TYPE_VAR); $i < $j; $i++) {
            xcache_clear_cache(XC_TYPE_VAR, $i);
        }
        return true;
    }

}

<?php

namespace Zeus\Cache;

class APC implements Proxy\CacheType
{

    /**
     * Fetchs a cached variable.
     * @param string $var The variable name.
     * @return mixed The cached variable or false if it doesn't exists.
     */
    public function fetch($var)
    {
        return apc_fetch($var);
    }

    /**
     * Stores a variable to cache.
     * @param string $var The variable name.
     * @param mixed $value The data to be stored in cache.
     * @param integer $time the amount of time before it expires.
     * @return bool TRUE on success or FALSE on failure.
     */
    public function store($var, $value, $time = Cache::CACHE_FIVE_MINUTES)
    {
        return apc_store($var, $value, $time);
    }

    /**
     * Deletes a cached variable.
     * @param string $var The variable name.
     * @return bool TRUE on success or FALSE on failure.
     */
    public function delete($var)
    {
        return apc_delete($var);
    }

    /**
     * Wipe all cached data.
     * @return bool TRUE always.
     */
    public function clear()
    {
        return apc_clear_cache();
    }

}

<?php

namespace Zeus\Cache\Proxy;

use Zeus\Cache\CacheTime;

interface CacheType
{

    public function fetch($var);

    public function store($var, $value, $time = CacheTime::ONE_HOUR);

    public function delete($var);

    public function clear();
}

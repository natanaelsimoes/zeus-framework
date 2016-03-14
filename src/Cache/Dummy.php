<?php

namespace Zeus\Cache;

class Dummy implements \Doctrine\Common\Cache\Cache
{

    public function fetch($id)
    {
        return false;
    }

    public function contains($id)
    {
        return false;
    }

    public function save($id, $data, $lifeTime = CacheTime::ONE_HOUR)
    {
        return false;
    }

    public function delete($id)
    {
        return false;
    }

    public function getStats()
    {
        return false;
    }

}

<?php

namespace Zeus;

/**
 * Defines a Singleton structure, meant to be extends by classes that
 * needs to be instantiated  just once during the execution
 */
abstract class Singleton
{

    /**
     * No singleton class can be cloned
     * @throws \Exception When you try to clone
     */
    final private function __clone()
    {
        throw new \Exception('Cannot clone a singleton class');
    }

    /**
     * Returns the static instance of the Singleton called class
     * @return Singleton
     */
    public static function getInstance()
    {
        static $instances = array();
        $calledClass = get_called_class();
        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass();
        }
        return $instances[$calledClass];
    }

}

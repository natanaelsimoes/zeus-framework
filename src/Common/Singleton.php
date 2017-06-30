<?php

namespace Zeus\Common;

/**
 * Defines a Singleton structure, meant to be extends by classes that
 * needs to be instantiated  just once during the execution
 */
abstract class Singleton
{

    /**
     * The instance of Singleton specialized class
     * @var Singleton
     */
    private static $instance = array();

    /**
     * No singleton class can be cloned
     * @throws \Exception When you try to clone
     */
    public function __clone()
    {
        throw new \Exception('Cannot clone a singleton class');
    }

    /**
     * Returns the static instance of the Singleton specialized class
     * @return Singleton
     */
    public static function getInstance()
    {
        $className = get_called_class();
        if (!isset(self::$instance[$className])) {
            self::$instance[$className] = new $className;
        }
        return self::$instance[$className];
    }

}

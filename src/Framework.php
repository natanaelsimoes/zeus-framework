<?php

namespace Zeus;

class Framework
{

    private static $instance;

    private function __construct()
    {
        self::initializeDatabase();
        self::evaluateRoute();
    }

    public function __clone()
    {
        throw new \Exception('Cannot clone a singleton class');
    }

    public static function getInstance()
    {
        throw new \Exception('You must use Zeus\Framework::start() function.');
    }

    public static function start()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    private static function initializeDatabase()
    {
        Database::getInstance();
    }

    private static function evaluateRoute()
    {
        Routes::getInstance()
                ->loadRoutes()
                ->evaluateURL();
    }

}

<?php

namespace Zeus;

class Framework extends Common\Singleton
{

    protected function __construct()
    {
        self::initializeDatabase();
        self::evaluateRoute();
    }

    public static function getInstance()
    {
        throw new \Exception('You must use Zeus\Framework::start() function.');
    }

    public static function start()
    {
        parent::getInstance();
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

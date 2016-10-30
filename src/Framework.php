<?php

namespace Zeus;

/**
 * Zeus Framework's main class
 */
class Framework extends \Singleton
{

    /**
     * Initialize database (if configured) and evaluate route given by URL
     * @todo include cache support before anything
     */
    private function __construct()
    {
        self::initializeDatabase();
        self::evaluateRoute();
    }

    /**
     * This class cannot be instantiated nor controlled, it is just a
     * start button to initiliaze the framework
     * @throws \Exception
     */
    public static function getInstance()
    {
        throw new \Exception('You must use Zeus\Framework::start() function.');
    }

    /**
     * Initialize the framework
     */
    public static function start()
    {
        parent::getInstance();
    }

    /**
     * Initialize the database connection
     */
    private static function initializeDatabase()
    {
        Database::getInstance();
    }

    /**
     * Evaluate route by given URL
     */
    private static function evaluateRoute()
    {
        Routes::getInstance()
                ->loadRoutes()
                ->evaluateURL();
    }

}

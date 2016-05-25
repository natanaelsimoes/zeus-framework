<?php

namespace Zeus;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

class Database
{

    private $entityManager;
    private static $instance;

    private function __construct()
    {
        $zConf = Configuration::getInstance();
        if (!is_null($zConf->getDatabase())) {
            $ormConfig = Setup::createAnnotationMetadataConfiguration(
                            array($zConf->getInitialDirectory())
                            , $zConf->inDevelopment());
            $this->entityManager = EntityManager::create(array(
                        'driver' => $zConf->getDatabase()->driver,
                        'user' => $zConf->getDatabase()->username,
                        'password' => $zConf->getDatabase()->password,
                        'dbname' => $zConf->getDatabase()->dbname,
                            ), $ormConfig);
        }
    }

    public function __clone()
    {
        throw new \Exception('Cannot clone a singleton class');
    }

    /**
     * @return Database
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    public function createSchema($overwrite = false)
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if ($overwrite) {
            $tool->dropSchema($classes);
        }
        $tool->createSchema($classes);
        return true;
    }

    public function updateSchema()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($classes);
        return true;
    }

    public function dropSchema()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        return true;
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return Database::getInstance()->entityManager;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

}

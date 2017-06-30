<?php

namespace Zeus;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

class Database extends Common\Singleton
{

    private $entityManager;

    protected function __construct(EntityManager $em = null)
    {
        if (is_null($em)) {
            $zConf = Configuration::getInstance();
            if (!is_null($zConf->getDatabase())) {
                $ormConfig = Setup::createAnnotationMetadataConfiguration(
                                array($zConf->getInitialDirectory())
                                , $zConf->inDevelopment());
                $this->entityManager = EntityManager::create(array(
                            'host' => $zConf->getDatabase()->host,
                            'driver' => $zConf->getDatabase()->driver,
                            'user' => $zConf->getDatabase()->username,
                            'password' => $zConf->getDatabase()->password,
                            'dbname' => $zConf->getDatabase()->dbname,
                                ), $ormConfig);
            } else {
                trigger_error("Database configuration not found at zeus.json"
                        , E_USER_WARNING);
            }
        } else {
            $this->entityManager = $em;
        }
    }

    /**
     * Creates the database using Doctrine Entities annotations
     * @param bool $overwrite Drop database then recreate everything fresh
     */
    public function createSchema(bool $overwrite = false)
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if ($overwrite) {
            $tool->dropSchema($classes);
        }
        $tool->createSchema($classes);
    }

    /**
     * Updates the database with latest changes on classes
     */
    public function updateSchema()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($classes);
    }

    /**
     * Drops the entire database
     */
    public function dropSchema()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
    }

    /**
     * Returns a configured Doctrine Entity Manager
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return Database::getInstance()->entityManager;
    }

}

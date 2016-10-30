<?php

namespace Zeus;

/**
 * This class is meant to be extended by others classes that needs to be
 * persisted into database. It implements methods for storing and recovering
 * objects. Specialized classes should be a Doctrine Entity.
 */
class Entity
{

    /**
     * Finds a object using its identificator
     * @param mixed $id The identificator value
     * @return Entity The object being searched
     */
    public static function find($id)
    {
        $em = Database::getEntityManager();
        return $em->find(get_called_class(), $id);
    }

    /**
     * Saves the object into database (inserting or updating)
     */
    public function save()
    {
        $em = Database::getEntityManager();
        $em->beginTransaction();
        $em->persist($this);
        $em->commit();
        $em->flush();
    }

    /**
     * Deletes the object from database
     */
    public function delete()
    {
        $em = Database::getEntityManager();
        $em->beginTransaction();
        $em->remove($this);
        $em->commit();
        $em->flush();
    }

    /**
     * Returns a Doctrine QueryBuilder initially with all rows
     * @param string $alias Class alias used within the QueryBuilder
     * @param mixed $fields List of attributes from the called class
     * @return \Doctrine\ORM\QueryBuilder
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html
     */
    public static function select($alias, $fields = null)
    {
        $em = Database::getEntityManager();
        return $em->createQueryBuilder()->select(is_null($fields) ? $alias : $fields)->from(get_called_class(), $alias);
    }

    /**
     * Returns a Doctrine Expression builder, for complex expressions
     * @return \Doctrine\ORM\Query\Expr
     */
    public static function expr()
    {
        $em = Database::getEntityManager();
        return $em->createQueryBuilder()->expr();
    }

}

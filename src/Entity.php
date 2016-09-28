<?php

namespace Zeus;

class Entity
{
    
    public function find($id) {
        $em = Database::getEntityManager();
        return $em->find(get_called_class(), $id);
    }

    public function save()
    {
        $em = Database::getEntityManager();
        $em->beginTransaction();
        $em->persist($this);
        $em->commit();
        $em->flush();
    }

    public function delete()
    {
        $em = Database::getEntityManager();
        $em->beginTransaction();
        $em->remove($this);
        $em->commit();
        $em->flush();
    }

    /**
     * 
     * @param string $alias
     * @param mixed $fields
     * @return \Doctrine\ORM\QueryBuilder
     */
    public static function select($alias, $fields = null)
    {
        $em = Database::getEntityManager();
        return $em->createQueryBuilder()->select(is_null($fields) ? $alias : $fields)->from(get_called_class(), $alias);
    }

    /**
     * @return \Doctrine\ORM\Query\Expr
     */
    public static function expr()
    {
        $em = Database::getEntityManager();
        return $em->createQueryBuilder()->expr();
    }

}

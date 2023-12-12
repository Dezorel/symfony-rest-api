<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
    public function getBooks()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b.id', 'b.title', 'a.name as author', 'b.description', 'b.price')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.author', 'a')
            ->setFirstResult(0)
            ->setMaxResults(1000);

        return $queryBuilder->getQuery()->getResult();
    }
}
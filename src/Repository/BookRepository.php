<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
    public function getBooks()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b')
            ->from($this->getClassName(), 'b')
            ->setFirstResult(0)
            ->setMaxResults(1000);

        return $queryBuilder->getQuery()->getResult();
    }
}
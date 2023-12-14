<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
    public function getBooks(int $page = 1, int $elementsPerPage = 10)
    {
        $page = $page ? $page - 1 : 0;

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b.id', 'b.title', 'a.name as author', 'b.description', 'b.price')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.author', 'a')
            ->setFirstResult($elementsPerPage * $page)
            ->setMaxResults($elementsPerPage);

        return $queryBuilder->getQuery()->getResult();
    }
}
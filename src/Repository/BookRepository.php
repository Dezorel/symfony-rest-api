<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
    public function getBooks(int $page = 0, int $elementsPerPage = 10)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b.id', 'b.title', 'a.name as author', 'b.description', 'b.price')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.author', 'a')
            ->setFirstResult($elementsPerPage * $page)
            ->setMaxResults($elementsPerPage);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getBookById(int $id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b.id', 'b.title', 'a.name as author', 'b.description', 'b.price')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.author', 'a')
            ->where('b.id = ' . $id);

        return $queryBuilder->getQuery()->getResult();
    }
}
<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

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

    /**
     * @param int $id
     * @return Book|null
     * @throws NonUniqueResultException
     */
    public function getBookById(int $id): ?Book
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.author', 'a')
            ->where('b.id = :id')
            ->setMaxResults(1)
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
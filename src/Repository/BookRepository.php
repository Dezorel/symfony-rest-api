<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class BookRepository extends EntityRepository
{
    /**
     * @param int $page
     * @param int $elementsPerPage
     * @return float|int|mixed|string
     */
    public function getBooks(int $page = 0, int $elementsPerPage = 10): mixed
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

    /**
     * @param int $page
     * @param int $elementsPerPage
     * @return float|int|mixed|string
     */
    public function getBookByAuthorName(string $authorName, int $page = 0, int $elementsPerPage = 10): mixed
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('b.id', 'b.title', 'a.name as author', 'b.description', 'b.price')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.author', 'a')
            ->where('a.name = :author')
            ->setParameter('author', $authorName)
            ->setFirstResult($elementsPerPage * $page)
            ->setMaxResults($elementsPerPage);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return float|int|mixed|string
     */
    public function deleteBookById(int $id): mixed
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->delete($this->getClassName(),'b')
            ->where('b.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getResult();
    }
}
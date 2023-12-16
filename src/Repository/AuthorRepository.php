<?php
namespace App\Repository;

use App\Entity\Author;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class AuthorRepository extends EntityRepository
{
    /**
     * @param string $name
     * @return Author|null
     * @throws NonUniqueResultException
     */
    public function getAuthorByName(string $name): ?Author
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('a')
            ->from($this->getClassName(), 'a')
            ->where('a.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);


        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getAuthors(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('a')
            ->from($this->getClassName(), 'a');

        return $queryBuilder->getQuery()->getResult();
    }
}
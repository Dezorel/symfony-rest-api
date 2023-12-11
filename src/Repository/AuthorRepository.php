<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository
{
    /**
     * return Author[]
     */
    public function getAuthors(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('a')
            ->from($this->getClassName(), 'a');

        return $queryBuilder->getQuery()->getResult();
    }
}
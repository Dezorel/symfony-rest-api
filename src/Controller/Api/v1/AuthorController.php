<?php

namespace App\Controller\Api\v1;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function test(): Response
    {
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $authors = $authorRepository->getAuthors();

        return $this->handleView($this->view($authors, Response::HTTP_OK));
    }
}
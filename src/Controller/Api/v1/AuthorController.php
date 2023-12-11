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

    /**
     * @Rest\Get("/api/v1/test")
     */
    public function getTestAuthor(): Response
    {
        $authorRepository = $this->entityManager->getRepository(Author::class);

        // Вызов метода getAuthors()
        $authors = $authorRepository->getAuthors();

        return $this->handleView($this->view(json_encode($authors), Response::HTTP_OK));
    }
}
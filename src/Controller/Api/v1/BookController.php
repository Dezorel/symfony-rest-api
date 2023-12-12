<?php

namespace App\Controller\Api\v1;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

class BookController extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/api/v1/book")
     */
    public function getTestAuthor(): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        // Вызов метода getAuthors()
        $book = $bookRepository->getBooks();

        return $this->handleView($this->view($book, Response::HTTP_OK));
    }
}
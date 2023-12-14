<?php

namespace App\Controller\Api\v1;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookController extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/api/books")
     */
    public function getBooks(Request $request): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        $page = $request->query->get('page', '0');

        $page = $page ? $page - 1 : 0;

        $books = $bookRepository->getBooks($page);

        return $this->handleView($this->view($books, Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/api/books/{id}")
     */
    public function getBookById(int $id): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        $book = $bookRepository->getBookById($id);

        return $this->handleView($this->view($book, Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/api/v1/test")
     */
    public function testApi(): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        $book = $bookRepository->getBooks();

        return $this->handleView($this->view($book, Response::HTTP_OK));
    }
}
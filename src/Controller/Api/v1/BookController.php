<?php

namespace App\Controller\Api\v1;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

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
     * @Rest\Post("/api/books")
     */
    public function createBook(Request $request): Response
    {
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $jsonData = json_decode($request->getContent(), true);

        if (isset($jsonData['title']) && isset($jsonData['price']))
        {
            $book = new Book();

            $book->setTitle($jsonData['title']);

            $book->setPrice($jsonData['price']);

            if (!$author = $authorRepository->getAuthorByName($jsonData['author_name']))
            {
                $author = new Author();

                $author->setName($jsonData['author_name']);

                $this->entityManager->persist($author);

                $this->entityManager->flush();
            }

            $book->setAuthor($author);

            if (isset($jsonData['description']))
            {
                $book->setDescription($jsonData['description']);
            }

            $this->entityManager->persist($book);

            $this->entityManager->flush();
        }

        return $this->handleView($this->view($jsonData, Response::HTTP_CREATED));
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
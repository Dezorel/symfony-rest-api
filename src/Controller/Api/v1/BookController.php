<?php

namespace App\Controller\Api\v1;

use App\Entity\Author;
use App\Entity\Book;
use App\Enums\ResponseCode;
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

        $responseData = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor()->getName(),
            'description' => $book->getDescription(),
            'price' => $book->getPrice(),
        ];

        return $this->handleView($this->view($responseData, Response::HTTP_OK));
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

        return $this->handleView(
            $this->view(ReponseController::generateSuccessResponse(ResponseCode::CREATED),
            Response::HTTP_CREATED)
        );
    }

    /**
     * @Rest\Put("/api/books/{id}")
     */
    public function updateBook(Request $request, int $id)
    {
        $availableToCreateAuthor = true;

        $bookRepository = $this->entityManager->getRepository(Book::class);
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $jsonData = json_decode($request->getContent(), true);

        if (!$book = $bookRepository->getBookById($id))
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                Response::HTTP_NOT_FOUND)
            );
        }

        if (!$author = $authorRepository->getAuthorByName($jsonData['author_name']))
        {
            if ($availableToCreateAuthor)
            {
                $author = new Author();

                $author->setName($jsonData['author_name']);

                $this->entityManager->persist($author);

                $this->entityManager->flush();
            }
            else
            {
                return $this->handleView(
                    $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                        Response::HTTP_NOT_FOUND)
                );
            }
        }

        if (isset($jsonData['title']))
        {
            $book->setTitle($jsonData['title']);
        }

        if (isset($jsonData['author_name']))
        {
            $book->setAuthor($author);
        }

        if (isset($jsonData['description']))
        {
            $book->setDescription($jsonData['description']);
        }

        if (isset($jsonData['price']))
        {
            $book->setPrice($jsonData['price']);
        }

        $this->entityManager->persist($book);

        $this->entityManager->flush();

        return $this->handleView(
            $this->view(ReponseController::generateSuccessResponseWithData(ResponseCode::SUCCESS, $book),
                Response::HTTP_CREATED)
        );
    }
}
<?php

namespace App\Controller\Api\v1;

use App\Entity\Author;
use App\Entity\Book;
use App\Enums\ResponseCode;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
    public function createBook(Request $request, ValidatorInterface $validator): Response
    {
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $jsonData = json_decode($request->getContent(), true);

        try
        {
            UtilityController::validateParam($validator, $jsonData, $this->getBookConstraint());
        }
        catch (Exception $e)
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::VALIDATION_FAIL, $e->getMessage()),
                    Response::HTTP_NOT_ACCEPTABLE)
            );
        }

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
    public function updateBook(Request $request, int $id): Response
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

        if (isset($jsonData['title']))
        {
            $book->setTitle($jsonData['title']);
        }

        if (isset($jsonData['author_name']))
        {
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

        $responseData = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor()->getName(),
            'description' => $book->getDescription(),
            'price' => $book->getPrice(),
        ];

        return $this->handleView(
            $this->view(ReponseController::generateSuccessResponseWithData(ResponseCode::SUCCESS, $responseData),
                Response::HTTP_OK)
        );
    }

    /**
     * @Rest\Delete("/api/books/{id}")
     */
    public function deleteBook(int $id): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        if (!$bookRepository->deleteBookById($id))
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                    Response::HTTP_NOT_FOUND)
            );
        }

        return $this->handleView(
            $this->view(ReponseController::generateSuccessResponse(ResponseCode::SUCCESS),
                Response::HTTP_OK)
        );
    }

    private function getBookConstraint(): Assert\Collection
    {
        return new Assert\Collection([
            'fields' => array_merge(
              $this->getTitleConstraint(),
              $this->getPriceConstraint(),
              $this->getAuthorConstraint(),
            ),
            'allowExtraFields' => false,
            'missingFieldsMessage' => 'The field {{ field }} is missing.',
        ]);
    }

    private function getTitleConstraint(): array
    {
        return [
            'title' => [
                new Assert\NotNull([
                    'message' => 'The title parameter cannot be null.',
                ]),
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'The title parameter must be an string.',
                ]),
            ],
        ];
    }

    private function getPriceConstraint(): array
    {
        return [
            'price' => [
                new Assert\NotNull([
                    'message' => 'The price parameter cannot be null.',
                ]),
                new Assert\Type([
                    'type' => 'float',
                    'message' => 'The price parameter must be an float.',
                ]),
            ],
        ];
    }

    private function getAuthorConstraint(): array
    {
        return [
            'author_name' => [
                new Assert\NotNull([
                    'message' => 'The author_name parameter cannot be null.',
                ]),
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'The author_name parameter must be an string.',
                ]),
            ],
        ];
    }
}
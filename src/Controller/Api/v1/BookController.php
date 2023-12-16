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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class BookController extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/api/books")
     *
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get a books",
     *     tags={"Books"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             ref=@Model(type=YourResourceType::class)
     *         )
     *     )
     * )
     */
    public function getBooks(Request $request): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        $page = $request->query->get('page', '0');

        $page = $page ? $page - 1 : 0;

        if (!$books = $bookRepository->getBooks($page))
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                    Response::HTTP_NOT_FOUND)
            );
        }

        return $this->handleView($this->view($books, Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/api/books/{id}")
     */
    public function getBookById(int $id): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        if (!$book = $bookRepository->getBookById($id))
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                    Response::HTTP_NOT_FOUND)
            );
        }

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

        $book = new Book();

        if (isset($jsonData['description']))
        {
            try
            {
                UtilityController::validateParam(
                    $validator,
                    ['description' => $jsonData['description']],
                    new Assert\Collection($this->getDescriptionConstraint())
                );

                $book->setDescription($jsonData['description']);
            }
            catch (\Exception $e)
            {
                return $this->handleView(
                    $this->view(ReponseController::generateFailedResponse(ResponseCode::VALIDATION_FAIL, $e->getMessage()),
                        Response::HTTP_NOT_ACCEPTABLE)
                );
            }
        }

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

        $this->entityManager->persist($book);

        $this->entityManager->flush();

        return $this->handleView(
            $this->view(ReponseController::generateSuccessResponse(ResponseCode::CREATED),
            Response::HTTP_CREATED)
        );
    }

    /**
     * @Rest\Put("/api/books/{id}")
     */
    public function updateBook(Request $request, ValidatorInterface $validator, int $id): Response
    {
        $availableToCreateAuthor = true;
        $isBookUpdated = false;

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

        try
        {
            if (isset($jsonData['title']))
            {
                UtilityController::validateParam(
                    $validator,
                    ['title' => $jsonData['title']],
                    new Assert\Collection($this->getTitleConstraint())
                );

                $book->setTitle($jsonData['title']);

                $isBookUpdated = true;
            }

            if (isset($jsonData['description']))
            {
                UtilityController::validateParam(
                    $validator,
                    ['description' => $jsonData['description']],
                    new Assert\Collection($this->getDescriptionConstraint())
                );

                $book->setDescription($jsonData['description']);

                $isBookUpdated = true;
            }

            if (isset($jsonData['price']))
            {
                UtilityController::validateParam(
                    $validator,
                    ['price' => $jsonData['price']],
                    new Assert\Collection($this->getPriceConstraint())
                );

                $book->setPrice($jsonData['price']);

                $isBookUpdated = true;
            }

            if (isset($jsonData['author_name']))
            {
                UtilityController::validateParam(
                    $validator,
                    ['author_name' => $jsonData['author_name']],
                    new Assert\Collection($this->getAuthorConstraint())
                );

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

                $isBookUpdated = true;
            }
        }
        catch (\Exception $e)
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::VALIDATION_FAIL, $e->getMessage()),
                    Response::HTTP_NOT_ACCEPTABLE)
            );
        }

        if (!$isBookUpdated)
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::MISSING_PARAMS),
                    Response::HTTP_NOT_ACCEPTABLE)
            );
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

    /**
     * @return Assert\Collection
     */
    private function getBookConstraint(): Assert\Collection
    {
        return new Assert\Collection([
            'fields' => array_merge(
              $this->getTitleConstraint(),
              $this->getPriceConstraint(),
              $this->getAuthorConstraint(),
            ),
            'allowExtraFields' => true,
            'missingFieldsMessage' => 'The field {{ field }} is missing.',
        ]);
    }

    /**
     * @return Assert\Type[][]
     */
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

    /**
     * @return Assert\Type[][]
     */
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
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'The price must be 0 or more.',
                ]),
            ],
        ];
    }

    /**
     * @return Assert\Type[][]
     */
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

    /**
     * @return Assert\Type[][]
     */
    private function getDescriptionConstraint(): array
    {
        return [
            'description' => [
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'The description parameter must be an string.',
                ]),
            ],
        ];
    }
}
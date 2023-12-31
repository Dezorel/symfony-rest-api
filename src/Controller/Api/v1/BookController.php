<?php

namespace App\Controller\Api\v1;

use App\Entity\Author;
use App\Entity\Book;
use App\Enums\ResponseCode;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
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
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get a list of books",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         example=2,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Filter by author name",
     *         required=false,
     *         example=2,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id",type="integer",example=1),
     *                 @OA\Property(property="title",type="string",example="Ut voluptatem cum."),
     *                 @OA\Property(property="author",type="string", example="Adella Kozey"),
     *                 @OA\Property(property="description",type="string",example="Ut et optio quo. Velit minus et dolores tempora nemo."),
     *                 @OA\Property(property="price",type="number",format="float",example=22.12)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Content not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code",type="integer",example=1404),
     *             @OA\Property(property="error_message",type="string",example="Content not found")
     *         )
     *     )
     * )
     */
    public function getBooks(Request $request): Response
    {
        $bookRepository = $this->entityManager->getRepository(Book::class);

        $page = $request->query->get('page', '0');
        $authorName = $request->query->get('author', '0');

        $page = $page ? $page - 1 : 0;

        if (!$authorName)
        {
            if (!$books = $bookRepository->getBooks( $page))
            {
                return $this->handleView(
                    $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                        Response::HTTP_NOT_FOUND)
                );
            }
        }
        else
        {
            if (!$books = $bookRepository->getBookByAuthorName($authorName, $page))
            {
                return $this->handleView(
                    $this->view(ReponseController::generateFailedResponse(ResponseCode::NOT_FOUND),
                        Response::HTTP_NOT_FOUND)
                );
            }
        }

        return $this->handleView($this->view($books, Response::HTTP_OK));
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a book by specify id",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id",type="integer",example=12),
     *             @OA\Property(property="title",type="string",example="Reprehenderit nihil aut consequatur nihil."),
     *             @OA\Property(property="author",type="string",example="Tre Kiehn"),
     *             @OA\Property(property="description",type="string",example="Corporis ut voluptatem ab omnis aliquam. Qui natus hic eaque fuga ut. Doloremque error quibusdam tenetur at magni repellat."),
     *             @OA\Property(property="price",type="number",format="float",example=69.76)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Content not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code",type="integer",example=1404),
     *             @OA\Property(property="error_message",type="string",example="Content not found")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a book",
     *     tags={"Books"},
     *     security={{"basicAuth": {}}},
     *     @OA\RequestBody(
     *         description="Request body",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Book title"),
     *             @OA\Property(property="author_name", type="string", description="Book's author name"),
     *             @OA\Property(property="description", type="string", description="Book's desctiption", nullable=true, example=null),
     *             @OA\Property(property="price", type="number",format="float", description="Book's price", example=69.76),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="integer", example=1001),
     *             @OA\Property(property="message", type="string", example="Created"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1000018)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=406,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code",type="integer",example=1406),
     *             @OA\Property(property="error_message",type="string",example="The field 'param' is missing")
     *         )
     *     ),
     * )
     */
    public function createBook(Request $request, ValidatorInterface $validator): Response
    {
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $jsonData = json_decode($request->getContent(), true);

        if (!$jsonData)
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::MISSING_PARAMS),
                    Response::HTTP_NOT_ACCEPTABLE)
            );
        }

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
            $this->view(ReponseController::generateSuccessResponseWithData(ResponseCode::CREATED, ['id' => $book->getId()]),
            Response::HTTP_CREATED)
        );
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update an existing book",
     *     tags={"Books"},
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Request body",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Book title", nullable=true),
     *             @OA\Property(property="author_name", type="string", description="Book's author name", nullable=true),
     *             @OA\Property(property="description", type="string", description="Book's desctiption", nullable=true, example=null),
     *             @OA\Property(property="price", type="number",format="float", description="Book's price", nullable=true, example=69.76),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code",type="integer",example=1000),
     *             @OA\Property(property="message",type="string",example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id",type="integer",example=1000015),
     *                 @OA\Property(property="title",type="string",example="This is best title"),
     *                 @OA\Property(property="author",type="string",example="van Tester"),
     *                 @OA\Property(property="description",type="string",example="Qui natus hic eaque fuga ut."),
     *                 @OA\Property(property="price",type="number",format="float",example=12.7)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Content not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code",type="integer",example=1404),
     *             @OA\Property(property="error_message",type="string",example="Content not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=406,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code",type="integer",example=1406),
     *             @OA\Property(property="error_message",type="string",example="The field 'param' is missing")
     *         )
     *     ),
     * )
     */
    public function updateBook(Request $request, ValidatorInterface $validator, int $id): Response
    {
        $availableToCreateAuthor = true;
        $isBookUpdated = false;

        $bookRepository = $this->entityManager->getRepository(Book::class);
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $jsonData = json_decode($request->getContent(), true);

        if (!$jsonData)
        {
            return $this->handleView(
                $this->view(ReponseController::generateFailedResponse(ResponseCode::MISSING_PARAMS),
                    Response::HTTP_NOT_ACCEPTABLE)
            );
        }

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
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Get a books",
     *     tags={"Books"},
     *     security={{"basicAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code",type="integer",example=1000),
     *             @OA\Property(property="message",type="string",example="Success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Content not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code",type="integer",example=1404),
     *             @OA\Property(property="error_message",type="string",example="Content not found")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/books/catalog",
     *     summary="Get a catalog book file",
     *     tags={"Catalog"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="integer", example=1000),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     example="http://192.168.0.7:8000/catalog/books_catalog.csv"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function generateBookCatalog(Request $request): Response
    {
        $fastCloseConnection = true;
        $bookRepository = $this->entityManager->getRepository(Book::class);

        $page = 0;

        $file = 'catalog/books_catalog_' . date('YmdHis') . '.csv';

        $handle = fopen($this->getParameter('kernel.project_dir').'/public/' . $file, 'w+');

        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();

        $response = [
            'file' => $scheme . '://' . $host . ':' . $port . '/' . $file
        ];

        if ($fastCloseConnection)
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(ReponseController::generateSuccessResponseWithData(ResponseCode::SUCCESS, $response));
            fastcgi_finish_request();
        }

        while (true)
        {
            if (!$books = $bookRepository->getBooks($page, 20000))
            {
                fclose($handle);

                return $this->handleView(
                    $this->view(ReponseController::generateSuccessResponseWithData(ResponseCode::CREATED, $response),
                        Response::HTTP_CREATED)
                );
            }

            foreach ($books as $book) {
                $csvLine = sprintf('"%s";"%s";', $book['title'], $book['price']) . PHP_EOL;

                fwrite($handle, $csvLine);
            }
            
            $page++;
        }
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
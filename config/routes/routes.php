<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use App\Controller\Api\v1\BookController;
use App\Controller\Api\Documentation\SwaggerController;

return function (RoutingConfigurator $routes) {

    $routes->add('get_documentation', '/api/doc')
        ->methods(['GET'])
        ->controller([SwaggerController::class, 'apiDocumentation']);

    $routes->add('get_books', '/api/books')
        ->methods(['GET'])
        ->controller([BookController::class, 'getBooks']);

    $routes->add('create_book', '/api/books')
        ->methods(['POST'])
        ->controller([BookController::class, 'createBook']);

    $routes->add('get_books_catalog', '/api/books/catalog')
        ->methods(['GET'])
        ->controller([BookController::class, 'generateBookCatalog']);

    $routes->add('get_book_by_id', '/api/books/{id}')
        ->methods(['GET'])
        ->controller([BookController::class, 'getBookById']);

    $routes->add('update_book', '/api/books/{id}')
        ->methods(['PUT'])
        ->controller([BookController::class, 'updateBook']);

    $routes->add('delete_book', '/api/books/{id}')
        ->methods(['DELETE'])
        ->controller([BookController::class, 'deleteBook']);
};


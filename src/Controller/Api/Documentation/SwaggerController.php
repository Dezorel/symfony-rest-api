<?php

namespace App\Controller\Api\Documentation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SwaggerController extends AbstractController
{
    public function apiDocumentation(): Response
    {
        // Assuming 'config/dump/swagger.json' is the path to your Swagger JSON
        $jsonUrl = $this->getParameter('kernel.project_dir').'/public/swagger.json';

        return $this->render('swagger-ui/index.html.twig', [
            'swagger_data' => [
                'spec' => json_decode(file_get_contents($jsonUrl), true),
            ],
        ]);
    }
}
<?php

namespace App\Controller\Api\Documantation;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SwaggerController extends AbstractController
{
    /**
     * @Route("/api/doc", name="api_doc", methods={"GET"})
     */
    public function apiDocumentation()
    {
        // Assuming 'config/dump/swagger.json' is the path to your Swagger JSON
        $jsonUrl = $this->getParameter('kernel.project_dir').'/public/swagger.json';

        return $this->render('index.html.twig', [
            'swagger_data' => [
                'spec' => json_decode(file_get_contents($jsonUrl), true),
            ],
        ]);
    }
}
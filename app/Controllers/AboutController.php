<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AboutController extends BaseController
{
    private const API_NAME = 'YOUR_PROJECT_NAME';

    private const API_VERSION = '1.0.0';

    public function handleAboutWebService(Request $request, Response $response): Response
    {

        $resources = [
            [
                'name'        => 'breweries',
                'uri'         => 'http://localhost/beers-api/breweries',
                'methods'     => ['GET'],
                'description' => 'Gets a list of zero or more brewery resources that match the request\'s filtering criteria.',
            ],

            [
                'name'        => 'distributors',
                'uri'         => 'http://localhost/beers-api/distributors',
                'methods'     => ['GET'],
                'description' => 'Gets a list of zero or more location resources that match the request\'s filtering criteria.',
            ],
        ];

        $data = array(
            'api' => self::API_NAME,
            'version' => self::API_VERSION,
            'about' => 'Welcome! This is a Web service that provides this and that...',
            'authors' => 'FrostyBee',
            'resources' => $resources,
        );

        return $this->renderJson($response, $data);
    }
}

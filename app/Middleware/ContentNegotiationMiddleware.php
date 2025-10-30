<?php

namespace App\Middleware;

use App\Exceptions\HttpNotAcceptableException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ContentNegotiationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Optional: Handle the incoming request
        // ...

        // Invoke the next middleware and get response
        $headerValueString = $request->getHeaderLine('Accept');
        if ($headerValueString == "application/json") {
            $response = $handler->handle($request);
        } else {
            throw new HttpNotAcceptableException($request);
        }

        // Optional: Handle the outgoing response
        // ...

        return $response;
    }
}

<?php
declare(strict_types=1);

namespace PhpRest2\Exception;

use PhpRest2\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandler implements ExceptionHandlerInterface
{
    public function render(\Throwable $e): Response
    {
        $statusCode = match($e) {
            $e instanceof HttpException
                => $e->getStatusCode(),
            $e instanceof \InvalidArgumentException
                => Response::HTTP_BAD_REQUEST,
            default
                => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

        $response = Application::getInstance()->make(Response::class);
        $response->setContent($e->getMessage());
        $response->setStatusCode($statusCode);

        return $response;
    }
}
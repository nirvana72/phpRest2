<?php
declare(strict_types=1);

namespace PhpRest2\Exception;

use Symfony\Component\HttpFoundation\Response;

interface ExceptionHandlerInterface
{
    public function render(\Throwable $e): Response;
}
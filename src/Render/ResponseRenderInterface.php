<?php
declare(strict_types=1);

namespace PhpRest2\Render;

use Symfony\Component\HttpFoundation\Response;

interface ResponseRenderInterface
{
    public function render(mixed $return): Response;
}
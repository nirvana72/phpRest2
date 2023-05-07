<?php
declare(strict_types=1);

namespace PhpRest2\Hook;

use Symfony\Component\HttpFoundation\{Request, Response};

interface HookInterface
{
    /**
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
}
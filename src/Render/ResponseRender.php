<?php
declare(strict_types=1);

namespace PhpRest2\Render;

use PhpRest2\Application;
use Symfony\Component\HttpFoundation\Response;

class ResponseRender implements ResponseRenderInterface
{
    public function render(mixed $return): Response
    {
        //直接返回Response时, 对return不再做映射
        if($return instanceof Response){ 
            return $return;
        }

        $response = Application::getInstance()->make(Response::class);
        
        if ($return !== null) {
            $value = json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $response->setContent($value);
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
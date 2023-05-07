<?php

use PhpRest2\Application;
use PhpRest2\Exception\{ExceptionHandlerInterface, ExceptionHandler};
use PhpRest2\Render\{ResponseRenderInterface, ResponseRender};
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Cache\Adapter\ApcuAdapter as ApcuCache;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

return [
    // 默认request对象来自 symfony
    // https://symfony.com/doc/current/components/http_foundation.html#accessing-request-data
    Request::class => \DI\factory([Application::class, 'createRequestFromSymfony']),
    // Response 对象
    Response::class => \DI\create(),
    // 默认输出处理器
    ResponseRenderInterface::class => \DI\autowire(ResponseRender::class),
    // 默认错误处理器
    ExceptionHandlerInterface::class => \DI\autowire(ExceptionHandler::class),
    // 缓存对象
    // https://learnku.com/docs/psr/psr-6-cache/1614
    ApcuCache::class => \DI\create(),
    // 日志对象
    // https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md
    LoggerInterface::class => \DI\factory(function (ContainerInterface $c) {
        $config = $c->get('LoggerConfig');
        $dateFormat = "Y-m-d H:i:s";
        $formatter = new \Monolog\Formatter\LineFormatter($config['format'], $dateFormat);
        $stream = new \Monolog\Handler\StreamHandler($config['path'], $config['level']);
        $stream->setFormatter($formatter);
        $monoLog = new \Monolog\Logger('main');
        $monoLog->pushHandler($stream);
        return $monoLog;
    }),
];
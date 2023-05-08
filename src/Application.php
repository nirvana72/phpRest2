<?php
declare(strict_types=1);

namespace PhpRest2;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\{Request, Response, ParameterBag};
use PhpRest2\Controller\ControllerBuilder;
use PhpRest2\Exception\ExceptionHandlerInterface;
use PhpRest2\Exception\{BadCodeException, BadRequestException};
use DI\FactoryInterface;

class Application implements ContainerInterface, FactoryInterface
{
    /**
     * 创建app对象
     */
    public static function create(string|array $config = []): Application {
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Definitions.php');
        $builder->addDefinitions($config);
        $builder->useAutowiring(false);
        $builder->useAttributes(true);
        // $builder->enableCompilation(__DIR__ . '/cache');

        $container = $builder->build();

        $request = $container->get(Request::class);
        if ($request->getMethod() === 'OPTIONS') {
            $response = $container->make(Response::class);
            $response->setStatusCode(200);
            $response->send();
            exit;
        }
        $app = new self();
        $app->container = $container;
        $app->unionId = md5(__DIR__);
        self::$instance = $app;
        return $app;
    }

    /**
     * 遍历加载物理文件controller
     * 
     * 只会加载 'Controller.php' 结尾的PHP文件
     * 
     * @param string $path controller所在目录
     * @param string $namespace controller所在命名空间
     */
    public function scanRoutesFromPath(string $path, string $namespace): void {
        $this->scanPath($path, $namespace, 'Controller.php', function(string $classPath) {
            // 遍历加载 controller 类, string $classPath controller命名空间全路径
            try {
                $controller = $this->get(ControllerBuilder::class)->build($classPath);
                if ($controller === null) return;
                $this->controllers[] = $classPath;
                foreach ($controller->actions as $actionName => $action) {
                    $this->routes[] = [
                        'method' => $action->method, 
                        'route'  => "{$controller->route}{$action->route}", 
                        'class'  => $classPath,
                        'action' => $actionName,
                    ];
                }
            } catch (\Throwable $e) {
                $exceptionHandler = $this->get(ExceptionHandlerInterface::class);
                $exceptionHandler->render($e)->send();
                exit;
            }
        });
    }

    /**
     * 遍历加载事件文件Listener
     * 
     * 只会加载 'Listener.php' 结尾的PHP文件
     * 
     * @param string $path Listener所在目录
     * @param string $namespace Listener所在命名空间
     */
    public function scanListenerFromPath(string $path, string $namespace): void {
        $this->scanPath($path, $namespace, 'Listener.php', function(string $classPath) {
            if (false === is_subclass_of($classPath, \PhpRest2\Event\EventInterface::class)) {
                throw new BadCodeException("{$classPath} 必须继承于 \PhpRest2\Event\EventInterface");
            }
            $ctlClass = $this->get($classPath);
            $events = call_user_func([$ctlClass, 'listen']);
            foreach($events as $event) {
                $this->events[$event][] = $classPath;
            }
        });
    }

    private function scanPath(string $filePath, string $namespace, string $fileEndStr, callable $callback): void {
        $d = dir($filePath);
        while (($entry = $d->read()) !== false){
            if ($entry == '.' || $entry == '..') { continue; }
            $path = $filePath . '/' . $entry;
            if (is_file($path)) {
                if (str_ends_with  ($entry, $fileEndStr)) {
                    $classPath = $namespace . '\\' . substr($entry, 0, -4);
                    $callback($classPath);                    
                }
            } else {
                $this->scanPath($path, $namespace . '\\' . $entry, $fileEndStr, $callback);
            }
        }
        $d->close();
    }

    public static function createRequestFromSymfony(): Request {
        $request = Request::createFromGlobals();
        $contentType = $request->headers->get('CONTENT_TYPE');
        $httpMethod  = $request->getMethod();
        if (str_starts_with($contentType, 'application/json') && in_array($httpMethod, ['POST', 'PUT'])) {
            $data = json_decode($request->getContent(), true) ?: [];
            $request->request = new ParameterBag($data);
        }
        return $request;
    }

    /**
     * 解析请求
     */
    public function dispatch(): void {
        $request = $this->get(Request::class);
        $httpMethod = $request->getMethod();
        $uri        = $request->getRequestUri();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            foreach(Application::getInstance()->routes as $route) {
                $r->addRoute($route['method'], $route['route'], [$route['class'], $route['action']]);
            }
        });

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        $next = function($request) use ($routeInfo, $httpMethod, $uri): Response {
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::FOUND:
                    if (count($routeInfo[2])) { // 支持 path 参数, 规则参考FastRoute
                        if (in_array($httpMethod, ['GET', 'DELETE'])) 
                            $request->query->add($routeInfo[2]);
                        else
                            $request->request->add($routeInfo[2]);
                    }
                    if (is_array($routeInfo[1])) {
                        list($classPath, $actionName) = $routeInfo[1];
                        $controller = Application::getInstance()->get(ControllerBuilder::class)->build($classPath);
                        $action = $controller->actions[$actionName];
                        $action->hooks = array_merge($controller->hooks, $action->hooks); // 合并class + method hook
                        return $action->invoke($request, $classPath, $actionName);
                    } elseif ($routeInfo[1] instanceof \Closure) { // 手动注册的闭包路由
                        return $routeInfo[1]($request);
                    } else {
                        throw new BadCodeException("无法解析路由");
                    }
                    break;
                case \FastRoute\Dispatcher::NOT_FOUND:
                    throw new BadRequestException("{$uri} 访问地址不存在");
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    throw new BadRequestException("{$uri} 不支持 {$httpMethod} 请求");                
                default:
                    throw new BadRequestException("unknown dispatch return {$routeInfo[0]}");
            }
        };

        foreach (array_reverse(Application::getInstance()->globalHooks) as $hookPath){
            $next = function($request) use ($hookPath, $next){
                return Application::getInstance()->get($hookPath)->handle($request, $next);
            };
        }

        try {
            $response = $next($request);
            $response->send();
        } catch (\Throwable $e) {
            $exceptionHandler = Application::getInstance()->get(ExceptionHandlerInterface::class);
            $exceptionHandler->render($e)->send();
        }
    }

    /** 
     * 所有controller类名
     * @var string[]
     * */
    private array $controllers = [];
    public function getControllers(): array {
        return $this->controllers;
    }

    /**
     * 唯一ID, 区分apcu缓存用
     */
    public string $unionId;

     /** 
     * DI 容器
     */
    private \DI\Container $container;

    /**
     * impl Psr\Container\ContainerInterface
     */
    public function get(string $id) {
        return $this->container->get($id);
    }

    /**
     * impl Psr\Container\ContainerInterface
     */
    public function has(string $id): bool {
        return $this->container->has($id);
    }

    /**
     * impl DI\FactoryInterface
     */
    public function make(string $name, array $parameters = []) : mixed  {
        return $this->container->make($name, $parameters);
    }
    
    /**
     * 单列
     */
    private static Application $instance;
    public static function getInstance() : Application {
        return self::$instance;
    }

    /** 
     * 所有路由信息
     * type #line 59
     * */
    public array $routes = [];

     /**
     * 全局Hook
     * @var string[] Hook类全命名空间
     */
    private array $globalHooks = [];
    public function addGlobalHook(string $classPath): void {
        $this->globalHooks[] = $classPath;
    }

     /** 
     * 所有注册的事件对象
     * */
    private array $events = [];
    public function getEvent($eventName) : array{
        return $this->events[$eventName] ?: [];
    }
}
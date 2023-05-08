<?php
declare(strict_types=1);

namespace PhpRest2\Controller;

use Symfony\Component\HttpFoundation\{Request, Response};
use PhpRest2\Application;
use PhpRest2\Render\ResponseRenderInterface;

class Action
{
    public string $tag = 'action';

    /**
     * httpMethod GET|POST|...
     */
    public string $method;

    /**
     * 路由
     */
    public string $route = '';

    /**
     * 方法名
     */
    public string $funcName = '';

    /**
     * 中文名 不设置注解默认方法名
     */
    public string $name = '';

    /**
     * 描述
     */
    public string $desc = '';
    public function getDesc(): string {
        if ($this->desc === '') return $this->name;
        return $this->desc;
    }

    /**
     * 参数集合
     * @var Param[]
     */
    public array $params = [];

    /**
     * [[$classPath, $params]]
     * [['\App\Hooks\TestHook', 'abc']]
     * @var string[][]
     */
    public array $hooks = [];

    /**
     * 执行action
     */
    public function invoke(Request $request, string $classPath, string $actionName): Response
    {
        $next = function($request) use ($classPath, $actionName) {
            $args = [];
            foreach ($this->params as $_ => $param) {
                $args[] = $param->getValueFromRequest($request);
            }
            $ctlClass = Application::getInstance()->get($classPath);
            $res = call_user_func_array([$ctlClass, $actionName], $args);
            $responseRender = Application::getInstance()->get(ResponseRenderInterface::class);
            return $responseRender->render($res);
        };

        foreach (array_reverse($this->hooks) as $_ => $h) {
            $next = function($request) use ($h, $next) {
                list($hookPath, $params) = $h;
                $hook = Application::getInstance()->make($hookPath, ['params' => $params]);
                return $hook->handle($request, $next);
            };
        }

        return $next($request);
    }
}
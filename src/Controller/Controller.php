<?php
declare(strict_types=1);

namespace PhpRest2\Controller;

class Controller
{
    public function __construct(string $classPath)
    {
        $this->classPath = $classPath;
    }

    public string $tag = 'controller';

    /**
     * 类命名空间(调用时实例化用)
     */
    public string $classPath;

    /**
     * 中文名 不设置注解默认类名
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
     * 路由 /xxx
     */
    public string $route = '';

    /**
     * 文件物理路径(验证缓存过期用)
     */
    public string $filePath = '';

    /**
     * 上次修改时间(验证缓存过期用)
     */
    public int $modifyTimespan = 0;

    /**
     * @var Action[]
     */
    public array $actions = [];

    /**
     * [[$classPath, $params]]
     * [['\App\Hooks\TestHook', 'abc']]
     * @var string[][]
     */
    public array $hooks = [];
}
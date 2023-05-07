<?php
declare(strict_types=1);

namespace PhpRest2\Entity;

use PhpRest2\Application;
use PhpRest2\Orm\AbstractOrmEntity;
use PhpRest2\Exception\BadRequestException;

class Entity extends AbstractOrmEntity
{
    public function __construct(string $classPath)
    {
        $this->classPath = $classPath;
    }

    public string $tag = 'entity';

    /**
     * 类命名空间(调用时实例化用)
     */
    public string $classPath;

    /**
     * 文件物理路径(验证缓存过期用)
     */
    public string $filePath;

    /**
     * 上次修改时间(验证缓存过期用)
     */
    public int $modifyTimespan;

    /**
     * 中文名字
     */
    public string $name;

    /**
     * 描述
     */
    public string $desc;

    /**
     * @var Property[]
     */
    public $properties = [];

    public function makeInstanceWithData(mixed $data, bool $withValidator = false, mixed &$obj = null) : mixed 
    {
        if ($obj === null) $obj = Application::getInstance()->make($this->classPath);
        foreach ($this->properties as $p) {
            $val = $p->getValueFromData($data, $withValidator);
            if ($val === null) {
                if (false === $p->hasDefaultValue && false === $p->allowsNull) {
                    throw new BadRequestException("实体类 {$this->classPath} 创建失败, 缺少属性值 {$p->varName}");
                }
            } else {
                $obj->{$p->varName} = $val;
            }
        }
        return $obj;
    }
}
<?php
declare(strict_types=1);

namespace PhpRest2\Entity;

use PhpRest2\Orm\AbstractOrmProperty;
use PhpRest2\Utils\ValueHandler;

final class Property extends AbstractOrmProperty
{
    public string $tag = 'property';

    /**
     * 变量名
     */
    public string $varName;
    
    /**
     * 描述
     */
    public string $desc = '';

    /**
     * 参数类型
     */
    public string $varType = 'mixed';

    /**
     * 是否数组
     */
    public bool $isArray = false;

    /**
     * 是否允许为空
     */
    public bool $allowsNull = false;

    /**
     * 是否有默认值
     */
    public bool $hasDefaultValue = false;

    /**
     * 验证正则
     */
    public string $rule = '';

    public function getValueFromData(mixed $data, bool $withValidator) : mixed
    {
        if (false === array_key_exists($this->varName, $data)) {
            return null;
        }

        $val = $data[$this->varName];

        if ($val === null) return null;

        $valueHandler = new ValueHandler(
            $this->varName, 
            $this->varType, 
            $this->rule, 
            $this->isArray,
            $withValidator,
        );

        return $valueHandler->getValue($val);
    }
}
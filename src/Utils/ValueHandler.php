<?php
declare(strict_types=1);

namespace PhpRest2\Utils;

use PhpRest2\Application;
use PhpRest2\Exception\BadRequestException;
use PhpRest2\Entity\EntityBuilder;

class ValueHandler
{
    public function __construct(
        private readonly string $varName,
        private readonly string $varType,
        private readonly string $rule,
        private readonly bool   $isArray,
        private readonly bool   $withValidator,
    ) {}

    public function getValue(mixed $val) : mixed {
        // 是否实体类参数
        $isEntity = str_contains($this->varType, '\\') || preg_match("/^[A-Z]{1}$/", $this->varType[0]);

        if ($isEntity && $this->isArray) {
            return $this->getEntityArrayResult($val, $this->varType);
        } elseif ($isEntity) {
            return $this->getEntityResult($val, $this->varType);
        } elseif ($this->isArray) {
            return $this->getArrayResult($val);
        }
        return $this->getNormalResult($val);
    }
    
    // 实体类数组
    private function getEntityArrayResult(mixed $val, string $classPath) : array {
        if (is_array($val) === false || \PhpRest2\isAssocArray($val) === true) {
            throw new BadRequestException("参数 {$this->varName} 不是数组");
        }
        $entityArray = [];
        foreach ($val as $v) {
            $entity = Application::getInstance()->get(EntityBuilder::class)->build($classPath);
            $entityArray[] = $entity->makeInstanceWithData($v, $this->withValidator);
        }
        return $entityArray;
    }

    // 普通实体类
    private function getEntityResult(mixed $val, string $classPath) : mixed {
        $entity = Application::getInstance()->get(EntityBuilder::class)->build($classPath);
        return $entity->makeInstanceWithData($val, $this->withValidator);
    }

    // 普通数组
    private function getArrayResult(mixed $val) : array {
        if (is_array($val) === false || \PhpRest2\isAssocArray($val) === true) {
            throw new BadRequestException("参数 {$this->varName} 不是数组");
        }
        if ($this->withValidator === true) {
            foreach ($val as $v) $this->validate($v);
        }
        return $val;
    }

    // 普通值
    private function getNormalResult(mixed $val) : mixed {
        if ($this->withValidator === true) {
            $this->validate($val);
        }
        
        return match($this->varType) {
            'int'    => intval($val),
            'float'  => floatval($val),
            'string' => strval($val),
            default  => $val,
        };
    }

    // 验证参数值
    private function validate(mixed $val): void {
        $rules = [];
        if ($this->varType === 'int')   $rules[] = '/^[-]?[0-9]+$/';
        if ($this->varType === 'float') $rules[] = '/^[-]?([0-9]*[.])?[0-9]+$/';
        $rules[] = $this->rule;

        foreach($rules as $rule) {
            if ($rule === '') continue;

            if ($rule[0] === '/') { // 正则
                if (preg_match($rule, strval($val)) === 0) {
                    throw new BadRequestException("参数 {$this->varName} 不匹配验证规则");
                }
            } else {
                $vld = new Validator([$this->varName => $val], [], 'zh-cn');
                $vld->myRule($rule, $this->varName);
                if($vld->validate() === false) {
                    $error = current($vld->errors())[0];
                    throw new BadRequestException($error);
                }
            }
        }
    }
}
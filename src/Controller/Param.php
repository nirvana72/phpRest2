<?php
declare(strict_types=1);

namespace PhpRest2\Controller;

use Symfony\Component\HttpFoundation\Request;
use PhpRest2\Exception\BadRequestException;
use PhpRest2\Utils\ValueHandler;

class Param
{
    public string $tag = 'param';
    
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
     * 是否可选参数
     */
    public bool $isOptional = false;

    /**
     * 默认值
     */
    public mixed $default = null;

    /**
     * 验证正则
     */
    public string $rule = '';
    
    /**
     * 定位request中位置 request.xxx | query.xxx
     */
    public string $bind = '';

    /**
     * 从Request中取值
     */
    public function getValueFromRequest(Request $request) : mixed
    {
        // 直接绑定Request对象
        if ($this->varType === Request::class) {
            return $request;
        }

        list($source, $name) = explode('.', $this->bind);
        $val = $request->{$source}->get($name, null);
        if ($val === null) {
            if ($this->isOptional) {
                return $this->default;
            } else {
                throw new BadRequestException("参数 {$this->bind} 不存在");
            }
        }

        $valueHandler = new ValueHandler(
            $this->varName, 
            $this->varType, 
            $this->rule, 
            $this->isArray,
            withValidator: true,
        );

        return $valueHandler->getValue($val);
    }
}
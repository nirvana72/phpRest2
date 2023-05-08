<?php
declare(strict_types=1);

namespace PhpRest2\Controller\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;
use PhpRest2\Exception\BadCodeException;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Param implements AttributeInterface
{
    public function __construct(
      public string $name,
      public string $type = '',
      public string $desc = '',
      public string $bind = '',
      // https://github.com/vlucas/valitron#built-in-validation-rules
      public string $rule = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        if ($target->tag === 'param') {
            if ($this->type !== '') {
                if ($target->varType !== 'mixed' && $target->varType !== $this->type) {
                    throw new BadCodeException("参数 {$target->varName} 类型描述不一至");
                }
                $target->varType = $this->type;
            }
            if ($this->desc !== '') $target->desc = $this->desc;
            if ($this->bind !== '') $target->bind = $this->bind;
            if ($this->rule !== '') $target->rule = $this->rule;
        }
    }
}
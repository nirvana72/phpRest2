<?php
declare(strict_types=1);

namespace PhpRest2\Entity\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;
use PhpRest2\Exception\BadCodeException;

#[Attribute]
class Property implements AttributeInterface
{
    public function __construct(
      public string $type = '',
      // https://github.com/vlucas/valitron#built-in-validation-rules
      public string $rule = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        if ($target->tag === 'property') {
            if ($this->type !== '') {
                if ($target->varType !== 'mixed' && $target->varType !== $this->type) {
                    throw new BadCodeException("实体类属性 {$target->varName} 类型描述不一至");
                }
                $target->varType = $this->type;
            }
            if ($this->rule !== '') $target->rule = $this->rule;
        }
    }
}
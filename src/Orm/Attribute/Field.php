<?php
declare(strict_types=1);

namespace PhpRest2\Orm\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute]
class Field implements AttributeInterface
{
    public function __construct(
      public string $name = '',
      public string $primaryKey = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        if ($target->tag === 'property') {
            if ($this->name !== '')       $target->field      = $this->name;
            if ($this->primaryKey !== '') $target->primaryKey = $this->primaryKey;
        }
    }
}
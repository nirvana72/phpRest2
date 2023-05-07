<?php
declare(strict_types=1);

namespace PhpRest2\Orm\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute]
class Table implements AttributeInterface
{
    public function __construct(
      public string $name = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        if ($target->tag === 'entity') {
            if ($this->name !== '') $target->table = $this->name;
        }
    }
}
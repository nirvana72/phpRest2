<?php
declare(strict_types=1);

namespace PhpRest2\Entity\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute]
class Summary implements AttributeInterface
{
    public function __construct(
        public string $name = '', 
        public string $desc = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        $target->desc = $this->desc;
        if ($target->tag === 'entity') {
            // 这个name 是中文名，property 的name是变量名，不用这个
            $target->name = $this->name;
        }
    }
}
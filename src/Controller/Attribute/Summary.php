<?php
declare(strict_types=1);

namespace PhpRest2\Controller\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Summary implements AttributeInterface
{
    public function __construct(
        public string $name = '', 
        public string $desc = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        $target->name = $this->name;
        $target->desc = $this->desc;
    }
}
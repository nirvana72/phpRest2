<?php
declare(strict_types=1);

namespace PhpRest2\Controller\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller implements AttributeInterface
{
    public function __construct(public string $route) {}

    public function bind2Target(mixed $target) : void {
        if ($target->tag === 'controller') {
            $target->route = $this->route;
        }
    }
}
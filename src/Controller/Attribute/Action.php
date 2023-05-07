<?php
declare(strict_types=1);

namespace PhpRest2\Controller\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class Action implements AttributeInterface
{
    public string $method;

    public string $route;

    // #[Action('GET:/')]
    public function __construct(string $val) {
        list($method, $route) = explode(':', $val);
        $this->method = $method;
        $this->route  = $route;
    }

    public function bind2Target(mixed $target) : void {
        if ($target->tag === 'action') {
            $target->method = $this->method;
            $target->route  = $this->route;
        }
    }
}
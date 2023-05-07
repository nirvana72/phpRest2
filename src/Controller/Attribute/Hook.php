<?php
declare(strict_types=1);

namespace PhpRest2\Controller\Attribute;

use Attribute;
use PhpRest2\AttributeInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Hook implements AttributeInterface
{
    // #[Hook(TestHook::class, params: 'abc')]
    public function __construct(
        public string $name,
        public string $params = '',
    ) {}

    public function bind2Target(mixed $target) : void {
        if (in_array($target->tag, ['controller', 'action'])) {
            $tag = md5($this->name);
            $target->hooks[$tag] = [$this->name, $this->params];
        }
    }
}
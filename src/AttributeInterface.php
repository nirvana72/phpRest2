<?php
declare(strict_types=1);

namespace PhpRest2;

interface AttributeInterface
{
    public function bind2Target(mixed $target) : void;
}
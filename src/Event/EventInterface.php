<?php
declare(strict_types=1);

namespace PhpRest2\Event;

interface EventInterface
{
    /**
     * 返回监听的事件集合
     * 
     * 事件键值为一个字符串
     * 
     * @return string[]
     */
    public function listen(): array;

    /**
     * 触发事件后的执行方法
     */
    public function handle(string $event, mixed $params = []): void;
}
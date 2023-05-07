<?php
declare(strict_types=1);

namespace PhpRest2\Event;

use PhpRest2\Application;

class EventTrigger
{
    public static function on(string $event, mixed $params = [])
    {
        $args['event']  = $event;
        $args['params'] = $params;
        $events = Application::getInstance()->getEvent($event);
        foreach ($events as $classPath) {
            $ctlClass = Application::getInstance()->get($classPath);
            call_user_func_array([$ctlClass, 'handle'], $args);
        }
    }
}

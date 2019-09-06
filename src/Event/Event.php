<?php

/*
 * This file is part of PHP CS Fixer.
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP\Event;

class Event
{
    //存储hander
    public static $events = [];

    //用于绑定事件
    public static function on($name, $handler, $params = [])
    {
        self::$events[$name] = [$handler, $params];
    }

    //用于解绑事件
    public static function off($event)
    {
        if (!empty(self::$events)) {
            foreach (self::$events as $name => $handler) {
                if ($event == $name) {
                    unset(self::$events[$name]);
                }
            }
        }
    }

    //用于触发事件
    public static function trigger($event)
    {
        if (!empty(self::$events)) {
            foreach (self::$events as $name => $handler) {
                if ($event == $name) {
                    call_user_func_array($handler[0], $handler[1]);
                    unset(self::$events[$name]);
                }
            }
        }
    }
}

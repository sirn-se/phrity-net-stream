<?php

/**
 * File for Net\Mock\Mock class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net\Mock;

use Closure;

/**
 * Net\Mock\Mock class.
 */
class Mock
{
    private static $callback;
    private static $counter;

    public static function setUp(): void
    {
        // Redirect to mock variants of all classes in this library.
        class_alias('Phrity\Net\Mock\SocketServer', 'Phrity\Net\SocketServer');
        class_alias('Phrity\Net\Mock\StreamFactory', 'Phrity\Net\StreamFactory');
        class_alias('Phrity\Net\Mock\SocketStream', 'Phrity\Net\SocketStream');
        class_alias('Phrity\Net\Mock\Stream', 'Phrity\Net\Stream');
    }

    public static function register(Closure $callback)
    {
        self::$callback = $callback;
        self::$counter = 0;
    }

    public static function callback(string $method, $params)
    {
        return call_user_func(self::$callback, self::$counter++, $method, $params);
    }
}

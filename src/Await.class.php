<?php

namespace Async;
require_once __DIR__ . "/Error.class.php";

if (!\class_exists("Async\Await")) {

  class Await {

    private static $current = null;
    private $env, $args, $list = array();


    /**
     * @method __construct Create an async instance and register it in await context.
     * @param $env {Closure.<$arg1 {*}, ...} is a function executed to register every async
     *    instances, the function has for list of parameters `$args` and set `$this`
     *    at value `$ctx` or at this instance of `Async\Await`.
     * @param $args {array|null} is the argument sent as list of parameters in `$env`
     *    (default=`null`).
     * @param $ctx {Object|null} is the context where `$env` is executed
     *    (default=`$this`).
     * @return {Async\Await} this instance.
     */

    function __construct(\Closure $env, $args = null, $ctx = null) {
      $this->env($env, $args, $ctx);
    }


    /**
     * @method env Register async instances.
     * @param $env {Closure.<$arg1 {*}, ...} is a function executed to register every async
     *    instances, the function has for list of parameters `$args` and set `$this`
     *    at value `$ctx` or at this instance of `Async\Await`.
     * @param $args {array|null} is the argument sent as list of parameters in `$env`
     *    (default=`null`).
     * @param $ctx {Object|null} is the context where `$env` is executed
     *    (default=`$this`).
     * @return {Async\Await} this instance.
     */

    function env(\Closure $env, $args = null, $ctx = null) {
      $env = \is_null($ctx) ? $env : $env->bindTo($ctx);
      $args = \is_array($args) ? $args : array();

      if (self::$current !== $this) {
        $prev = self::$current;
        self::$current = $this;
        \call_user_func_array($env, $args);
        while (\count($this->list)) {
          $i = 0;
          while (isset($this->list[$i])) {
            $async = $this->list[$i];
            if ($async instanceof Async) $async->test();
            $i++;
          }
          \time_nanosleep(0, 1);
        }
        self::$current = $prev;
      } else {
        \call_user_func_array($env, $args);
      }

      return $this;
    }


    /**
     * @static add Add an async instance in the current await context, that will
     *    test async every tick.
     * @param $async {Async\Async} instance to test each tick.
     * @throws {Async\AsyncError} if there is no await context available.
     */

    static function add(Async $async) {
      if (!self::isAwaitContext()) throw new AsyncError("This async instance should be in an await context");
      \array_push(self::$current->list, $async);
    }


    /**
     * @static remove Remove an async instance from current await context; when
     *    there is no more async in an await context, script is no more blocked in
     *    this waiting loop.
     * @param $async {Async\Async} instance to remove of this await context.
     * @throws {Async\AsyncError} if there is no await context available.
     */

    static function remove(Async $async) {
      if (!self::isAwaitContext()) throw new AsyncError("This async instance should be in an await context");
      $key = \array_search($async, self::$current->list);
      \array_splice(self::$current->list, $key, 1);
    }


    /**
     * @static isAwaitContext This function verify that script is currently in an
     *    await context.
     * @return {bool} `true` if script is in an await context.
     */

    static function isAwaitContext() {
      return !!self::$current;
    }
  }
}

<?php

namespace Async;
require_once __DIR__ . "/Error.class.php";

class Await {

  private static $current = null;
  private $env, $args, $list = array();


  /**
   * @method __construct
   */

  function __construct($env, $args = null, $ctx = null) {
    $this->env($env, $args, $ctx);
  }


  /**
   * @method env Register async instances.
   * @param $env {Closure|callable} is a function executed to register every async
   *    instances, the function has for list of parameters `$args` and set `$this`
   *    at value `$ctx` or at this instance of `Async\Await`.
   * @param $args {array|null} is the argument sent as list of parameters in `$env`
   *    (default=`null`);
   * @param $ctx {Object|null} is the context where `$env` is executed
   *    (default=`$this`)
   * @return {Async\Await} this instance.
   * @throws {Async|AsyncError} if first parameter is not a callable.
   */

  function env($env, $args = null, $ctx = null) {
    if (!\is_object($ctx)) $ctx = $this;
    
    if (\is_callable($env)) $env = \Closure::bind($env, $ctx);
    else throw new AsyncError("First parameter is not callable");

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
   * @param $async {Async\Async} instance tested each tick.
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
   * @param $async {Async\Async} instance to
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

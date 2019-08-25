<?php

namespace Async;
require_once __DIR__ . "/Error.class.php";

if (!\class_exists("Async\Promise")) {

  /**
   * @class Promise Helps to flat async functions in a single instance.
   */

  class Promise {
    private $ctx = null,
      $result = null,
      $then = array(),
      $catch = array(),
      $finally = array(),
      $isResolved = false,
      $isRejected = false;


    /**
     * @method __construct Create a promise instance.
     * @param $fn {Closure.<$arg1 {*}, ...>} is the function executed that wait for
     *    `$resolve` execution or `$reject` execution or an error thrown.
     * @param $args {array|null} is the third parameter sent in `$fn` function.
     * @param $ctx {object|null} is the context where `$fn` is executed (which mean set `$this` value) (default=`$this`).
     * @return {Async\Promise} new instance.
     * @throws {Async\AsyncError} if first parameter is not a Closure.
     */

    function __construct ($fn, $args = null, $ctx = null) {
      if (!($fn instanceof \Closure)) throw new AsyncError("First parameter should be a Closure");
      if (!\is_array($args)) $args = array();
      if (!\is_object($ctx)) $ctx = $this;
      $this->ctx = $ctx;

      $fn = $fn->bindTo($ctx);
      $success = (function ($result = null) { $this->run_then($result); })->bindTo($this);
      $failure = (function ($error = null) { $this->run_catch($error); })->bindTo($this);

      try {
        \call_user_func($fn, $success, $failure, $args);
      } catch (\Throwable $err) {
        $fail->call($this, $err);
      }
    }


    /**
     * @method then Add a function to execute when `resolve` event happen, or
     *    execute it immediatly if promise instance is already resolved.
     * @param $then {Closure.<$arg1 {*}, ...>} is the function to register.
     * @return {Async\Promise} self instance.
     * @throws {Async\ASyncError} if first parameter is not a Closure.
     */

    function then ($then) {
      if ($then instanceof \Closure) {
        if ($this->isResolved) {
          try {
            $then->call($this->ctx, $this->result);
          } catch (\Throwable $err) {
            $this->run_catch($err);
          }
        } else {
          \array_push($this->then, $then);
        }
        return $this;
      } else {
        throw new AsyncError("First parameter should be a Closure");
      }
    }


    /**
     * @method catch Add a function to execute when `reject` event happen, or
     *    execute it immediatly if promise instance is already rejected.
     * @param $catch {Closure.<$arg1 {*}, ...>} is the function to register.
     * @return {Async\Promise} self instance.
     * @throws {Async\ASyncError} if first parameter is not a Closure.
     */

    function catch($catch) {
      if ($catch instanceof \Closure) {
        if ($this->isRejected) {
          $catch->call($this->ctx, $this->result);
        } else {
          \array_push($this->catch, $catch);
        }
        return $this;
      } else {
        throw new AsyncError("First parameter should be a Closure");
      }
    }


    /**
     * @method finally Add a function to execute after `resolve` or `reject` event
     *    happen, or execute it immediatly if promise instance is already done
     *    (resolved or rejected).
     * @param $finally {Closure.<$arg1 {*}, ...>} is the function to register.
     * @return {Async\Promise} self instance.
     * @throws {Async\ASyncError} if first parameter is not a Closure.
     */

    function finally ($finally) {
      if ($finally instanceof \Closure) {
        if ($this->isDone()) {
          $finally->call($this->ctx, $this->result);
        } else {
          \array_push($this->finally, $finally);
        }
        return $this;
      } else {
        throw new AsyncError("First parameter should be a Closure");
      }
    }

    private function run_then ($result = null) {
      if (!$this->isResolved && !$this->isRejected) {
        $this->isRejected = !($this->isResolved = true);
        $this->result = $result;
        try {
          while($then = \array_shift($this->then)) $then->call($this->ctx, $result);
          return $this->run_finally($result);
        } catch (\Throwable $err) {
          return $this->run_catch($err);
        }
      } else {
        return $this;
      }
    }

    private function run_catch ($error = null) {
      if (!$this->isResolved && !$this->isRejected) {
        $this->isResolved = !($this->isRejected = true);
        $this->result = $error;
        if (count ($this->catch)) {
          while($catch = \array_shift($this->catch)) $catch->call($this->ctx, $error);
        } else if ($error instanceof \Throwable) {
          throw $error;
        } else {
          throw new AsyncException("Unexpected error in Promise environment");
        }
        return $this->run_finally($result, $this->ctx);
      } else {
        return $this;
      }
    }

    private function run_finally ($result = null, $ctx = null) {
      while($finally = \array_shift($this->finally)) $finally->call($this->ctx, $result);
      return $this;
    }


    /**
     * @method isDone Verify if instance is done (resolved or rejected); when a
     *    promise instance is done, new registrations of `then` or `catch` or `finally`
     *    functions will be executed immediatly.
     * @return {bool} `true` if this instance is resolved or rejected.
     */

    function isDone () {
      return $this->isResolved || $this->isRejected;
    }


    /**
     * @static resolve Create a promise instance that immediatly resolve with
     *    `$result` as result in `then` functions. It is helpful when a promise
     *    is expected but you already have the result.
     * @param $result {*} value of result in `then`.
     * @return {Async\Promise} new instance already resolved.
     */

    static function resolve ($result = null) {
      return new self(function ($resolve, $_, $args) { $resolve($args); }, $result);
    }

    /**
     * @static reject Create a promise instance that immediatly reject with
     *    `$error` as error in `catch` functions. It is helpful when a promise
     *    is expected but you already have the error it should throw.
     * @param $error {*} value of error in `catch`.
     * @return {Async\Promise} new instance already rejected.
     */

    static function reject ($error = null) {
      return new self(function ($_, $reject, $args) { $reject($args); }, $error);
    }
  }
}

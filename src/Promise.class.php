<?php

namespace Async;
require_once __DIR__ . "/Error.class.php";

if (!\class_exists("Async\Promise")) {

  /**
   * @class Promise Helps to flat async functions in a single instance.
   */

  class Promise {
    private $result = null,
     $then = array(),
     $catch = array(),
     $finally = array(),
     $isResolved = false,
     $isRejected = false;


    /**
     * @method __construct Create a promise instance.
     * @param $fn {Closure|callable} is the function executed that wait for
     *    `$resolve` execution or `$reject` execution or an error thrown.
     * @param $args {array|null} is the third parameter sent in `$fn` function.
     * @param $ctx {object|null} is the context where `$fn` is executed (which mean set `$this` value) (default=`$this`).
     * @return {Async\Promise} new instance.
     * @throws {Async\AsyncError} if first parameter is not callable.
     */

    function __construct ($fn, $args = null, $ctx = null) {
      if (!\is_callable($fn)) throw new AsyncError("First parameter should be a callable");
      if (!\is_array($args)) $args = array();
      if (!\is_object($ctx)) $ctx = $this;

      $success = \Closure::bind(function ($result = null) {
        $this->run_then($result, $this);
      }, $this);

      $fail = \Closure::bind(function ($err = null) {
        $this->run_catch($err, $this);
      }, $this);

      try {
        \call_user_func($fn, $success, $fail, $args);
      } catch (\Throwable $err) {
        $fail->call($this, $err);
      }
    }


    /**
     * @method then Add a function to execute when `resolve` event happen, or
     *    execute it immediatly if promise instance is already resolved.
     * @param $then {Closure|callable} is the function to register.
     * @return {Async\Promise} self instance.
     * @throws {Async\ASyncError} if first parameter is not a callable.
     */

    function then ($then) {
      if (\is_callable($then)) {
        \array_push($this->then, $then);
        if ($this->isResolved) {
          try {
            $then($this->result);
          } catch (\Throwable $err) {
            $this->run_catch($err);
          }
        }
        return $this;
      } else {
        throw new AsyncError("First parameter should be a callable");
      }
    }


    /**
     * @method catch Add a function to execute when `reject` event happen, or
     *    execute it immediatly if promise instance is already rejected.
     * @param $catch {Closure|callable} is the function to register.
     * @return {Async\Promise} self instance.
     * @throws {Async\ASyncError} if first parameter is not a callable.
     */

    function catch($catch) {
      if (\is_callable($catch)) {
        \array_push($this->catch, $catch);
        if ($this->isRejected) {
          $catch($this->result);
        }
        return $this;
      } else {
        throw new AsyncError("First parameter should be a callable");
      }
    }


    /**
     * @method finally Add a function to execute after `resolve` or `reject` event
     *    happen, or execute it immediatly if promise instance is already done
     *    (resolved or rejected).
     * @param $finally {Closure|callable} is the function to register.
     * @return {Async\Promise} self instance.
     * @throws {Async\ASyncError} if first parameter is not a callable.
     */

    function finally ($finally) {
      if (\is_callable($finally)) {
        \array_push($this->finally, $finally);
        if ($this->isDone()) {
          $finally($this->result);
        }
        return $this;
      } else {
        throw new AsyncError("First parameter should be a callable");
      }
    }

    private function run_then ($result = null, $ctx = null) {
      if (!$this->isResolved && !$this->isRejected) {
        $this->isRejected = !($this->isResolved = true);
        $this->result = $result;
        try {
          while($then = \array_shift($this->then)) \call_user_func($then, $result);
          return $this->run_finally($result, $ctx);
        } catch (\Throwable $err) {
          return $this->run_catch($err, $ctx);
        }
      } else {
        return $this;
      }
    }

    private function run_catch ($err = null, $ctx = null) {
      if (!$this->isResolved && !$this->isRejected) {
        $this->isResolved = !($this->isRejected = true);
        $this->result = $err;
        if (count ($this->catch)) {
          while($catch = \array_shift($this->catch)) \call_user_func($catch, $err);
        } else if ($err instanceof \Throwable) {
          throw $err;
        } else {
          throw new AsyncException("Unexpected error in Promise environment");
        }
        return $this->run_finally($result, $ctx);
      } else {
        return $this;
      }
    }

    private function run_finally ($result = null, $ctx = null) {
      while($finally = \array_shift($this->finally)) \call_user_func($finally, $result);
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

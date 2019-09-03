<?php

namespace Async;
require_once __DIR__ . "/Error.class.php";

if (!\class_exists("Async\Promise")) {

  /**
   * @class Promise Helps to flat async functions in a single instance.
   */

  class Promise {
    private
      $result = null,
      $then = array(),
      $catch = array(),
      $finally = array(),
      $isResolved = false,
      $isRejected = false;


    /**
     * @method __construct Create a promise instance.
     * @param $fn {Closure.<$resolve {Closure.<$result {*}>}, $reject {Closure.<$error {*}>}>}
     *    is the function executed that wait for`$resolve` execution or `$reject`
     *    execution or an error thrown.
     * @return {Async\Promise.<$result {*}>.<$error {*}>} new instance.
     */

    function __construct (\Closure $fn) {
      $success = (function ($result = null) { $this->run_then($result); })->bindTo($this);
      $failure = (function ($error = null) { $this->run_catch($error); })->bindTo($this);

      try {
        \call_user_func($fn, $success, $failure);
      } catch (\Throwable $err) {
        $failure->call($this, $err);
      }
    }


    /**
     * @method then Add a function to execute when `resolve` event happen, or
     *    execute it immediatly if promise instance is already resolved.
     * @param $then {Closure.<$result {*}>} is the function to register.
     * @return {Async\Promise.<$result {*}>.<$error {*}>} self instance.
     */

    function then (\Closure $then) {
      if ($this->isResolved) {
        try {
          $then($this->result);
        } catch (\Throwable $err) {
          $this->run_catch($err);
        }
      } else {
        \array_push($this->then, $then);
      }
      return $this;
    }


    /**
     * @method catch Add a function to execute when `reject` event happen, or
     *    execute it immediatly if promise instance is already rejected.
     * @param $catch {Closure.<$error {*}>} is the function to register.
     * @return {Async\Promise.<$result {*}>.<$error {*}>} self instance.
     */

    function catch(\Closure $catch) {
      if ($this->isRejected) {
        $catch($this->result);
      } else {
        \array_push($this->catch, $catch);
      }
      return $this;
    }


    /**
     * @method finally Add a function to execute after `resolve` or `reject` event
     *    happen, or execute it immediatly if promise instance is already done
     *    (resolved or rejected).
     * @param $finally {Closure.<$result {*}>} is the function to register.
     * @return {Async\Promise.<$result {*}>.<$error {*}>} self instance.
     */

    function finally (\Closure $finally) {
      if ($this->isDone()) {
        $finally($this->result);
      } else {
        \array_push($this->finally, $finally);
      }
      return $this;
    }


    private function run_then ($result = null) {
      if (!$this->isResolved && !$this->isRejected) {
        $this->isRejected = !($this->isResolved = true);
        $this->result = $result;
        try {
          while($then = \array_shift($this->then)) $then($result);
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
          while($catch = \array_shift($this->catch)) $catch($error);
        } else if ($error instanceof \Throwable) {
          throw $error;
        } else {
          throw new AsyncException("Unexpected error in Promise environment");
        }
        return $this->run_finally($result);
      } else {
        return $this;
      }
    }


    private function run_finally ($result = null) {
      while($finally = \array_shift($this->finally)) $finally($result);
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
     * @return {Async\Promise.<$result {*}>.<>} new instance already resolved.
     */

    static function resolve ($result = null) {
      return new self(function ($resolve, $_) use ($result) { $resolve($result); });
    }


    /**
     * @static reject Create a promise instance that immediatly reject with
     *    `$error` as error in `catch` functions. It is helpful when a promise
     *    is expected but you already have the error it should throw.
     * @param $error {*} value of error in `catch`.
     * @return {Async\Promise.<>.<$error {*}>} new instance already rejected.
     */

    static function reject ($error = null) {
      return new self(function ($_, $reject) use ($error) { $reject($error); });
    }


    /**
     * @static all Verify all items of list of promises are done to resolve.
     * @param $proms {array} list of promises.
     * @return {Async\Promise.<>.<$error {*}>} new instance
     */

    static function all (array $proms) {
      return new self(function ($resolve) use ($proms) {
        async(function () use ($resolve, $proms) {
          foreach ($proms as $prom) {
            if ($prom instanceof Promise && !$prom->isDone()) return false;
          }
          $resolve();
          return true;
        });
      });
    }


    /**
     * @static any Verify any item of list of promises is done to resolve.
     * @param $proms {array} list of promises.
     * @return {Async\Promise.<>.<$error {*}>} new instance
     */

    static function any (array $proms) {
      return new self(function ($resolve) use ($proms) {
        async(function () use ($resolve, $proms) {
          foreach ($proms as $prom) if ($prom instanceof Promise && $prom->isDone()) {
            $resolve();
            return true;
          }
          return false;
        });
      });
    }
  }
}

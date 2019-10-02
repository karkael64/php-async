<?php

namespace Async;
require_once __DIR__ . "/Error.class.php";

if (!\class_exists("Async\Async")) {

  class Async {

    private $fn, $then;

    /**
     * @method __construct Register a function that wait to be resolved or rejected
     *    each ticks of `await` environment or a timeout error (3000ms if not unset).
     * @param Closure.<> $fn is a first function executed each ticks
     *    of `await` environment until it returns a truthfully value, sent in
     *    `$then` as second parameter; if function throws an error, send it to
     *    `$then` as first parameter.
     * @param Closure.<Throwable|null $error, mixed $result> $then is the function
     *    executed right after an event happened in `$fn` ; when `$fn` resolves, the
     *    result is sent as second parameter `$result` or when `$fn` throws error, the
     *    error is sent as first parameter `$error` (else `$error` is `null`).
     * @return Async\Async new instance.
     * @throws Async\AsyncError if this instance is not created in an `await` context.
     */

    function __construct(\Closure $fn, \Closure $then = null) {
      $this->fn = $fn;
      $this->then = $then;
      Await::add($this);
    }


    /**
     * @method test Verify that the `$fn` function returns a truthfully answer: if it
     *    is truthfully, execute `$then` function; else do nothing; or catch error
     *    and send it to `$then` function.
     * @return Async\Async this instance.
     */

    function test() {
      try {
        if ($res = ($this->fn)()) {
          Await::remove($this);
          if (!\is_null($this->then)) ($this->then)(null, $res);
        }
      } catch (\Throwable $err) {
        Await::remove($this);
        if (!\is_null($this->then)) ($this->then)($err, null);
      }
      return $this;
    }
  }
}

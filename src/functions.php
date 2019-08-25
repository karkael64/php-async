<?php

require_once __DIR__ . "/Await.class.php";
require_once __DIR__ . "/Async.class.php";
require_once __DIR__ . "/Promise.class.php";


/**
 * @function await Create an async instance and register it in await context.
 * @param $env {Closure|callable} is a function executed to register every async
 *    instances, the function has for list of parameters `$args` and set `$this`
 *    at value `$ctx` or at this instance of `Async\Await`.
 * @param $args {array|null} is the argument sent as list of parameters in `$env`
 *    (default=`null`).
 * @param $ctx {Object|null} is the context where `$env` is executed
 *    (default=`$this`).
 * @return {Async\Await} this instance.
 * @throws {Async|AsyncError} if first parameter is not a callable.
 */

function await($env, $args = null, $ctx = null) {
  new Async\Await($env, $args, $ctx);
}


/**
 * @function async Register a function that wait to be resolved or rejected
 *    each ticks of `await` environment or a timeout error (3000ms if not unset).
 * @param $fn {Closure.<$arg1 {*}, ...>} is a first function executed each ticks
 *    of `await` environment until it returns a truthfully value, sent in
 *    `$then` as second parameter; if function throws an error, send it to
 *    `$then` as first parameter.
 * @param $then {Closure.<$error {Throwable|null}, $result {*}>} is the function
 *    executed right after an event happened in `$fn` ; when `$fn` resolves, the
 *    result is sent as second parameter `$result` or when `$fn` throws error, the
 *    error is sent as first parameter `$error` (else `$error` is `null`).
 * @param $args {array|null} the list arguments sent to `$fn`.
 * @param $ctx {Object} the context where `$fn` and `$then` are executed (it
 *    means `$this` will be `$ctx` value).
 * @return {Async\Async} new instance.
 * @throws {Async\AsyncError} if first parameter is not callable.
 * @throws {Async\AsyncError} if second parameter is not callable.
 * @throws {Async\AsyncError} if this instance is not created in an `await` context.
 */

function async($fn, $then, $args = null, $ctx = null) {
  new Async\Async($fn, $then, $args, $ctx);
}

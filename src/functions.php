<?php

require_once __DIR__ . "/Await.class.php";
require_once __DIR__ . "/Async.class.php";
require_once __DIR__ . "/Promise.class.php";


/**
 * @function await Create an async instance and register it in await context.
 * @param $env {Closure.<>} is a function executed to register every async
 *    instances.
 * @return {Async\Await} this instance.
 */

function await(\Closure $env) {
  new Async\Await($env);
}


/**
 * @function async Register a function that wait to be resolved or rejected
 *    each ticks of `await` environment or a timeout error (3000ms if not unset).
 * @param $fn {Closure.<>} is a first function executed each ticks
 *    of `await` environment until it returns a truthfully value, sent in
 *    `$then` as second parameter; if function throws an error, send it to
 *    `$then` as first parameter.
 * @param $then {Closure.<$error {Throwable|null}, $result {*}>} is the function
 *    executed right after an event happened in `$fn` ; when `$fn` resolves, the
 *    result is sent as second parameter `$result` or when `$fn` throws error, the
 *    error is sent as first parameter `$error` (else `$error` is `null`).
 * @return {Async\Async} new instance.
 * @throws {Async\AsyncError} if this instance is not created in an `await` context.
 */

function async(\Closure $fn, \Closure $then = null) {
  new Async\Async($fn, $then);
}

# PHP Async methods

## Asynchronous paradigm for PHP!

It's now available! Just start your asynchronous environment with `await` function, then do whatever you want with functions and classes below!

## Installation

You can :

1. run `composer require amonite/async`, or
2. copy `async.phar` file in your project directory and load it with a `require`.

## Context

As a JavaScript developer, I like the asynchronous paradigm. I decided to export this paradigm to allow PHP to take advantage of it: now, produce libraries that can process information while waiting for them to load data.

## Non-blocking events

In PHP, by default many functions stop script until the end. That's helpful with common arithmetic functions, but you wan also observe it when you request a data from your database (no matter your software), when you load a file from your computer or internet, when you use some socket, or run a command-line.

When your script wait for an other software, then your process is down. When a client start a request to the server, he waits for the previous client end of script. When thousands clients wait for a single client who waits for an only one heavy SQL request with 30+ seconds duration, then the server could crash as if it was a DDOS attack.

By using this library, I will develop a non-blocking HTTP server, then HTTPS, HTTP/2, SQL and filesystem reader/writer. Hope it will help!

# Usage

1. [Understand await/async environment](#understand-awaitasync-environment)
    1. [Creating await environment](#creating-await-environment)
    2. [Triggering asynchronous events](#triggering-asynchronous-events)
    3. [Example: asynchronous socket](#example-asynchronous-socket)
2. [Use Promise class](#use-promise-class)
3. [Use Async class](#use-async-class)
4. [Use a combo of await and async](#use-a-combo-of-await-and-async)

## Understand await/async environment

Function `await` create an environment that allows to register an `async` event. An asynchronous event is an non-blocking function that continue script until end of a writing file function, a reading file function, connection start function and so on.

### Creating await environment

The function `$fn_env` in `await($fn_env) : mixed` create an asynchronous environment, and wait (many ticks) for all asynchronous events ends. The script is blocked in this function until the end of its async functions. If `$fn_env` returns a `Promise`, the `await` function returns its anwser resolved or throw its error rejected.

File wrapper `test/wrap.php` :
``` php
<?php

require_once "async.phar";
//require_once "index.php";

await(function () {

  require "await.php";
  require "async.php";

});

```

### Triggering asynchronous events

The function `$test` in `async($test, $then) : null` is executed each tick of `await` function until it returns a truthfully value (or throws an error).

Here the file `wrap` let all required files to use async functions.

In the next file, I combined both async and await. First async is registered, then we wait for `await` end of function and async events, and then register the last async function.

File `test/await.php` :
``` php
<?php

$date = microtime(true);

async(function () use ($date) { if($date + 4 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "first resolved\n"; });
echo "first entered\n";

await(function () use ($date) {

  async(function () use ($date) { if($date + 3 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "second resolved\n"; });
  echo "second entered\n";

  async(function () use ($date) { if($date + 2 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "third resolved\n"; });
  echo "third entered\n";

});

async(function () use ($date) { if($date + 1 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "fourth resolved\n"; });
echo "fourth entered\n";
```

The console will print:

```
(t+0s) > first entered
(t+0s) > second entered
(t+0s) > third entered
(t+2s) > third resolved
(t+3s) > second resolved
(t+3s) > fourth entered
(t+3s) > fourth resolved
(t+4s) > first resolved
```


## Use Promise class

A Promise is commonly used to flat asynchronous and verbose functions. It follows JavaScript standard.

File `test/promise.php` :
``` php
<?php

require_once __DIR__ . "/../index.php";
$date = microtime(true);

$p = new Async\Promise(function ($resolve, $reject) use ($date) {
  while (microtime(true) < $date + 2) time_nanosleep(0,1);
  $resolve();
});

$p->then(function () { echo "I waited for 2 seconds.\n"; throw new Error("test error"); });
$p->catch(function () { echo "An error occurred in the promise or in then() function.\n"; });


var_dump(await(function () use ($date) {
  return Async\Promise::all(array(
    Async\Promise::resolve(3.14),
    Async\Promise::async(function () { return "Fibonacci"; }),
    Async\Promise::async(function () use ($date) {
      if (microtime(true) > $date + 3) return "Time elapsed!";
    })
  ));
}));
```

(!) In this example, the code is blocked until the 2 seconds passed, then wait for every `Promise` to be resolved.

## Use Async class

If you want to full manage your async events, you may prefer use the class Async.

File `test/async.php` :
``` php
<?php

$delay = microtime(true) + 3;

$test = function () use ($delay) {
  if ($delay < microtime(true)) return "delay";
};

$then = function ($err, $m) {
  if ($err) {
    echo "$err err";
  } else {
    echo "$m then\n";
  }
};

new Async\Async($test, $then);
```

## The return in a blocking context

Now you've been played with asynchronous items, you can return in a synchronous script, with its blocking function and its wasting of time. Lucky you! You can get `await` value by returning a `Promise` in its environment. Function `await` is able to get result of a Promise returned. Warning: if your Promise reject, then `await` will throw the error!

File `test/await_async.php`:
``` php
<?php

require_once "index.php";

$res = await(function () {

  // do async stuff
  // ...

  return new Async\Promise(function (\Closure $resolve, \Closure $reject) {

    $d = 3;
    $t = microtime(true) + $d;
    async(function () use ($t){
      return microtime(true) > $t;
    }, function ($err) use ($resolve, $reject, $d) {
      if ($err) $reject($err);
      else $resolve("Now $d seconds elapsed. Well done!\n");
    });

  });
});

echo $res;
```

# Documentation


## Function `await`

Create an async instance and register it in await context.

* _Syntax:_ `await (\Closure $env) : mixed`
* _Parameter:_ `$env {Closure.<$self {Closure}>}` is a function executed to register every async instances.
* _Returns:_ `{*|null}` if `$env` returns a Promise resolved, return its result.
* _Throws:_ `{Throwable}` if `$env` throws or returns a Promise rejected (return its error).


## Function `async`

Register a function that wait to be resolved or rejected each ticks of `await` environment or a timeout error (3000ms if not unset).

* _Syntax:_ `async (\Closure $test, \Closure $then = null) : null`
* _Parameters:_
  * `$fn {Closure.<>}` is a first function executed each of `await` environment until it returns a truthfully value, sent in `$then` as second parameter; if function throws an error, send it to `$then` as first parameter.
  * `$then {Closure.<$error {Throwable|null}, $result {*}>}` is the function executed right after an event happened in `$fn` ; when `$fn` resolves, the result is sent as second parameter `$result` or when `$fn` throws error, the error is sent as first parameter `$error` (else `$error` is `null`).
* _Returns:_ `null`.
* _Throws:_ `{Async\AsyncError}` if this instance is not created in an `await` context.

____

## Class `Async\Await`

### Method `__construct`

Create an async instance and register it in await context.

* _Syntax:_ `$await = new Async\Await (\Closure $env) : {Async\Await}`
* _Parameter:_ `$env {Closure.<$self {Closure}>}` is a function executed to register every async instances.
* _Returns:_ `{Async\Await}` new instance.
* _Throws:_ `{Throwable}` if `$env` throws.


### Method `env`

Run function in await context, and add async instances found in `$env`.

* _Syntax:_ `$await->env (\Closure $env) : mixed`
* _Parameter:_ `$env {Closure.<$self {Closure}>}` is a function executed to register every async instances.
* _Returns:_ `{*|null}` if `$env` returns a Promise resolved, return its result.
* _Throws:_ `{Throwable}` if `$env` throws or returns a Promise rejected (return its error).


### Static `isAwaitContext`

Tell if your script is currently in an `await` context.

* _Syntax:_ `Async\Await::isAwaitContext() : boolean`
* _Returns:_ `{boolean}` `true` if script is in an await context.


### Static `add`


### Static `remove`


____

## Class `Async\Async`

### Method `__construct`

Register a function that wait to be resolved or rejected each ticks of `await` environment or a timeout error (3000ms if not unset).

* _Syntax:_ `$async = new Async\Async (\Closure $test, \Closure $then = null) : Async\Async`
* _Parameters:_
  * `$test {Closure.<>}` is a first function executed each of `await` environment until it returns a truthfully value, sent in `$then` as second parameter; if function throws an error, send it to `$then` as first parameter.
  * `$then {Closure.<$error {Throwable|null}, $result {*}>}` is the function executed right after an event happened in `$test` ; when `$test` resolves, the result is sent as second parameter `$result` or when `$test` throws error, the error is sent as first parameter `$error` (else `$error` is `null`).
* _Returns:_ `{Async\Async}` new instance.
* _Throws:_ `{Async\AsyncError}` if this instance is not created in an `await` context.

### Method `test`

Verify that the `$test` function returns a truthfully answer: if it is truthfully, execute `$then` function; else do nothing; or catch error and send it to `$then` function.

* _Syntax:_ `$async->test()`
* _Returns:_ `{Async\Async}` this instance.


____

## Class `Async\Promise`

### Method `__construct`

Create a Promise instance. Promise helps to flat async functions in a single instance.

* _Syntax:_ `$prom = new Async\Promise(\Closure $fn) : Async\Promise`
* _Parameter:_ `$fn {Closure.<$resolve {Closure.<$result {*}>}, $reject {Closure.<$error {Throwable}>}>}` is the function executed that wait for`$resolve` execution or `$reject` execution or an error thrown.
* _Returns:_ `{Async\Promise.<$result {*}>.<$error {Throwable}>}` new instance.


### Method `then`

Register a function to execute when Promise resolves.

* _Syntax:_ `$prom->then(\Closure $fn)`
* _Parameter:_ `$fn {Closure.<$result {*}>}` is the function executed when Promise is resolved. The `$result` value is sent in `$resolve`.
* _Returns:_ `{Async\Promise}` self instance.

### Method `catch`

Register a function to execute when Promise rejects.

* _Syntax:_ `$prom->then(\Closure $fn)`
* _Parameter:_ `$fn {Closure.<$error {Throwable}>}` is the function executed when Promise is rejected. The `$error` value is sent in `$reject`.
* _Returns:_ `{Async\Promise}` self instance.

### Method `finally`

Register a function to execute after Promise resolves or rejects.

* _Syntax:_ `$prom->then(\Closure $fn)`
* _Parameter:_ `$fn {Closure.<$result {*}>}` is the function executed after Promise is resolved or rejected. The `$result` value is sent in `$resolve` or in `$reject`.
* _Returns:_ `{Async\Promise}` self instance.

### Static `resolve`

Helpful function for creating an already resolved Promise.

* _Syntax:_ `Async\Promise::resolve($result {*})`
* _Parameter:_ `$result {*}` is the value resolved
* _Returns:_ `{Async\Promise}` new instance.

### Static `reject`

Helpful function for creating an already rejected Promise.

* _Syntax:_ `Async\Promise::resolve($error {*})`
* _Parameter:_ `$error {Throwable}` is the value rejected
* _Returns:_ `{Async\Promise}` new instance.

### Static `all`

Helpful function for creating a Promise that wait for list `$list` of Promise to be resolved.

* _Syntax:_ `Async\Promise::all($list)`
* _Parameter:_ `$list {Array.<{Async\Promise} ...>}` a list of Promises that should be resolved.
* _Returns:_ `{Array.<{*} ...>}` the answer of each Promises.

### Static `any`

Helpful function for creating a Promise that wait for one Promise of list `$list` of Promise to be resolved.

* _Syntax:_ `Async\Promise::any($list)`
* _Parameter:_ `$list {Array.<{Async\Promise} ...>}` a list of Promises that could be resolved.
* _Returns:_ `{*}` the answer of the first resolved Promise.

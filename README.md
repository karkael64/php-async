# PHP Async methods

## Asynchronous paradigm for PHP!

It's now available! Just start your asynchronous environment with `await` function, then do whatever you want with functions and classes below!

## Installation

You can :

1. run `composer install amonite/async`, or
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

The function `$fn_env` in `await($fn_env, $args, $ctx) : null` create an asynchronous environment, and wait (many ticks) for all asynchronous events ends. The script is blocked in this function until the end. The `$args` is sent in the function as list of parameters.

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

The function `$test` in `async($test, $then, $args, $ctx) : null` is executed each tick of await function until it returns a truthfully value (or throws an error). The `args` are sent in `$test` function.

The file `wrap` let all required files to use async functions.

In the next file, I combined both async and await. First async is registered, then we wait for `await` end of function and async events, and then register the last async function.

File `test/await.php` :
``` php
<?php

$date = microtime(true);

async(function ($date) { if($date + 4 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "first resolved\n"; }, array($date), 0);
echo "first entered\n";

await(function ($date) {

  async(function ($date) { if($date + 3 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "second resolved\n"; }, array($date), 0);
  echo "second entered\n";

  async(function ($date) { if($date + 2 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "third resolved\n"; }, array($date), 0);
  echo "third entered\n";

}, array($date), 0);

async(function ($date) { if($date + 1 < microtime(true)) return true; }, function ($err) { if ($err) echo "err:$err\n"; echo "fourth resolved\n"; }, array($date), 0);
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

A Promise is commonly used to flat asynchronous and verbose functions. It follows JavaScript standard but also add `$args` to send it in Promise function as parameter.

File `test/promise.php` :
``` php
<?php

require_once "../src/Promise.class.php";
$date = microtime(true);
$args = array($date);

$p = new Async\Promise(function ($resolve, $reject, $args) {
  $date = $args[0];
  while (microtime(true) < $date + 2) time_nanosleep(0,1);
  $resolve();
}, $args);

$p->then(function () { echo "I waited for 2 seconds.\n"; });
$p->catch(function () { echo "An error occurred in the promise or in then() function.\n"; });
```

(!) In this example, the code is blocked until the 2 seconds passed.

## Use Async class

If you want to full manage your async events, you may prefer use the class Async.

File `test/async.php` :
``` php
<?php

$delay = microtime(true) + 3;

$test = function ($delay) {
  if ($delay < microtime(true)) return "delay";
};

$then = function ($err, $m) {
  if ($err) {
    echo "$err err";
  } else {
    echo "$m then\n";
  }
};

new Async\Async($test, $then, array($delay));
```

# Documentation

## Function `await(\Closure $fn_env, $args = null, $context = null) : null`

_Parameters:_
* `$fn_env($args = null, Async\Await $self)` (Closure) is the function that register its async events, the function has for first parameter `$args` and second parameter `$self` the current instance of `Async\Await`;
* `$args` (*) is the argument sent as first parameter in `$fn_env` (default=`null`);
* `$delay` (int) set a delay in milliseconds until await environment send an error timeout, set to 0 to ignore (default=`3000`).

_Returns:_ `Async\Await` new instance.

_Throws:_
* `Async\AsyncError` if first parameter is not a Closure.

Creates an environment in the function `$fn_env` where you can create async functions. The environment block script until every async functions ends or at timeout (3000ms if not unset).


## Function `async(\Closure $expect, \Closure $then, $args = null, $context = null) : null`

_Parameters:_
* `$expect($arg1 = null, ...)` (Closure) is a first function executed (where `$args` is the list of `$expect` parameters) each ticks of `await` environment until it return a truthfully value, sent in `$then` as second parameter; if function throws an error, send it to `$then` as first parameter;
* `$then($error = null, $result = null)` (Closure) is the function executed when event happened, where `$error` is the error thrown in `$expect` and `$result` is the truthfully value returned by `$expect`;
* `$args` (*) is the argument sent as third parameter in `$expect` functions (default=`null`);
* `$context` (null|Object) is the object context in where these functions are executed (`$this` has for value `$context` in `$expect` and `$then` functions execution).*

_Returns:_ `Async\Async` new instance.

_Throws:_
* `Async\AsyncError` if first parameter is not a Closure ;
* `Async\AsyncError` if this instance is not created in an `await` environment.

Register a function that wait to be resolved or rejected each ticks of `await` environment or a timeout error (3000ms if not unset).


## Class `Async\Await`
### Construct `$await = new Async\Await(\Closure $env, array $args = null, int $delay = 3000) : Async\Await`

_Parameters:_
* `$env($arg1 = null, ...)` (Closure) is the function that register its async events, the function has for first parameter `$args` and second parameter `$self` the current instance of `Async\Await`;
* `$args` (array|null) is the argument sent as first parameter in `$env` (default=`null`);
* `$delay` (int) set a delay in milliseconds until await environment send an error timeout, set to 0 to ignore (default=`3000`).

_Returns:_ `Async\Await` new instance.

_Throws:_
* `Async\AsyncError` if first parameter is not a Closure.

Creates an environment in the function `$env` where you can create async functions. The environment block script until every async functions ends or at timeout (3000ms if not unset).


### Method `$await->add(Async\Async $async) : Async\Await`

_Parameter:_
* `$async` (Async\Async) register an async instance in this `await` environment and execute its abstracted `test` method each tick.

_Returns:_ `Async\Await` self instance.

This function register your `Async\Async` child class (like `Async\Async` class) in the `await` environment. The `execute` method of your `Async\Async` instance will be executed until you run `remove` with this instance as parameter.


### Method `$await->remove(Async\Async $async) : Async\Await`

_Parameter:_
* `$async` (Async\Async) unregister an async instance in this `await` environment.

_Returns:_ `Async\Await` self instance.

This function unregister your `Async\Async` in this `await` environment. When there is no more `Async\Async` registered, the `await` environment dies and let blocked script to continue.


## Class `Async\Async`

### Construct `$async = new Async\Async(\Closure $expect, \Closure $then, $args = null, $context = null) : Async\Async`

_Parameters:_
* `$expect($arg1 = null, ...)` (Closure) is a first function executed (where `$args` is the list of `$expect` parameters) each ticks of `await` environment until it return a truthfully value, sent in `$then` as second parameter; if function throws an error, send it to `$then` as first parameter;
* `$then($error = null, $result = null)` (Closure) is the function executed when event happened, where `$error` is the error thrown in `$expect` and `$result` is the truthfully value returned by `$expect`;
* `$args` (*) is the argument sent as third parameter in `$expect` functions (default=`null`);
* `$context` (null|Object) is the object context in where these functions are executed (`$this` has for value `$context` in `$expect` and `$then` functions execution).

_Returns:_
* `Async\Async` new instance.

_Throws:_
* `Async\AsyncError` if first parameter is not a Closure ;
* `Async\AsyncError` if this instance is not created in an `await` environment.

Register a function that wait to be resolved or rejected each ticks of `await` environment or a timeout error (3000ms if not unset).


### Method `$async->test() : null`

_Parameter:_ none

_Returns:_ `null`

Test expectations `expect` of this `async` instance. When you overrides this function, don't forget: when you resolve or reject, tell the script to remove this `Async\Async` instance from its `await` environment ; it's also recommended to trigger an end function.


____

## Class `Async\Promise`
### Construct `$prom = new Async\Promise(\Closure $fn, $args = null)`

_Parameters:_
* `$fn(\Closure $resolve($result = null), \Closure $reject($error = null), $args = null, Async\Promise $self)` (Closure) is executed right there and wait for a resolve or a reject event, where `$args` is sent to third parameter of `$fn` ;
* `$args` (*) is the parameter sent as third parameter in `$fn` (default=`null`).

_Return:_ `Async\Promise` new instance.

_Throws:_
* `Async\AsyncError` if first parameter is not a Closure ;
* `Async\AsyncError` if promise trigger `catch` event but there is no `catch` function registered.

Create a `Async\Promise` instance. It helps to flat asynchronous functions.

### Method `$prom->then(\Closure $then) : Async\Async`

_Parameter:_
* `$then($args = null)` (Closure) is called when this `Async\Promise` instance is resolved.

_Returns:_ `Async\Promise` self instance.

_Throws:_ `Async\AsyncError` if first parameter is not a Closure.

Register another function executed when this instance is resolved.


### Method `$prom->catch(\Closure $catch) : Async\Async`

_Parameter:_
* `$catch($args = null)` (Closure) is called when this `Async\Promise` instance is rejected or when `then` functions throws an error.

_Returns:_ `Async\Promise` self instance.

_Throws:_ `Async\AsyncError` if first parameter is not a Closure.

Register another function executed when this instance is resolved or when `then` functions throws an error.


### Method `$prom->finally(\Closure $finally) : Async\Async`

_Parameter:_
* `$finally($args = null)` (Closure) is called when this `Async\Promise` instance ends after a resolve or a reject event.

_Returns:_ `Async\Promise` self instance.

_Throws:_ `Async\AsyncError` if first parameter is not a Closure.

Register another function executed when this instance ends after a resolve or a reject event.


### Static method `Async\Promise::resolve($args = null) : Async\Async`

_Parameter:_
* `$args` (*) is the argument sent as result of resolve event (default=`null`).

_Returns:_ `Async\Promise` new instance.

This static method helps to create a `Async\Promise` instance that resolves immediately. It is commonly used when a Promise instance is expected as parameter and you already have the response.


### Static method `Async\Promise::reject($args = null) : Async\Async`

_Parameter:_
* `$args` (*) is the argument sent as result of reject event (default=`null`).

_Returns:_ `Async\Promise` new instance.

This static method helps to create a `Async\Promise` instance that rejects immediately. It is commonly used when a Promise instance is expected as parameter and you already have the error.

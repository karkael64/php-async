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

${summary(2)}

## Understand await/async environment

Function `await` create an environment that allows to register an `async` event. An asynchronous event is an non-blocking function that continue script until end of a writing file function, a reading file function, connection start function and so on.

### Creating await environment

The function `$fn_env` in `${definition('await')}` create an asynchronous environment, and wait (many ticks) for all asynchronous events ends. The script is blocked in this function until the end. The `$args` is sent in the function as list of parameters.

File wrapper `test/wrap.php` :
``` php
${file_contents('./test/wrap.php')}
```

### Triggering asynchronous events

The function `$test` in `${definition('async')}` is executed each tick of await function until it returns a truthfully value (or throws an error). The `args` are sent in `$test` function.

The file `wrap` let all required files to use async functions.

In the next file, I combined both async and await. First async is registered, then we wait for `await` end of function and async events, and then register the last async function.

File `test/await.php` :
``` php
${file_contents('./test/await.php')}
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
${file_contents('./test/promise.php')}
```

(!) In this example, the code is blocked until the 2 seconds passed.

## Use Async class

If you want to full manage your async events, you may prefer use the class Async.

File `test/async.php` :
``` php
${file_contents('./test/async.php')}
```

# Documentation

${summary(3)}

${documentation()}

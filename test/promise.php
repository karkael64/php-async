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

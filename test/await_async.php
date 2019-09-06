<?php

require_once "index.php";

$res = await(function () {
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

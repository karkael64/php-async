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

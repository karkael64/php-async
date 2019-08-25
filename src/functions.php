<?php

require_once __DIR__ . "/Await.class.php";
require_once __DIR__ . "/Async.class.php";
require_once __DIR__ . "/Promise.class.php";

function await($env, $args = null, $ctx = null) {
  new Async\Await($env, $args, $ctx);
}

function async($fn, $then, $args = null, $ctx = null) {
  new Async\Async($fn, $then, $args, $ctx);
}

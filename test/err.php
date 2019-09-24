<?php

namespace Any;
class Error extends \Error {}

require_once "index.php";

\await(function () {
  \async(function () {
    throw new Error("test", -123);
  }, function ($err) {
    echo "catched:$err";
  });
});

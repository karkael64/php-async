<?php

namespace Async;

if (!\class_exists("Async\AsyncError")) {
  class AsyncError extends \Error {}
}

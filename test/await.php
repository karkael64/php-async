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

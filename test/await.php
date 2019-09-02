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

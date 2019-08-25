<?php

$this_file = $argv[0];
$rank_version = isset($argv[1]) ? $argv[1] : null;

$timestamp = microtime(true) * 1000;
$composer_file = __DIR__ . "/composer.json";
$composer = json_decode(file_get_contents($composer_file), true);
$init_version = $composer["version"];

if ($rank_version) {
	$ms = $timestamp % 1000;
	$composer["timestamp"] = $timestamp;
	$composer["date"] = date("Y-m-d\TH:i:s.") . $ms . "Z";
	$version = explode(".", $composer["version"]);
	switch($rank_version) {
		case "m":
		case "minor":
		case "fix":
			$version[2]++;
			break;
		case "f":
		case "mid":
		case "medium":
		case "func":
			$version[1]++;
			$version[2] = 0;
			break;
		case "M":
		case "major":
		case "refacto":
			$version[0]++;
			$version[1] = 0;
			$version[2] = 0;
			break;
	}
	$composer["version"] = implode(".", $version);
	file_put_contents($composer_file, json_encode($composer, JSON_PRETTY_PRINT));
}

echo "Building..." . PHP_EOL;

$f = "async.phar";
if (file_exists($f))
	unlink($f);

$p = new Phar($f);

$files = array(
  __DIR__ . "/src/Error.class.php",
  __DIR__ . "/src/Await.class.php",
  __DIR__ . "/src/Async.class.php",
  __DIR__ . "/src/Promise.class.php",
	__DIR__ . "/src/functions.php"
);

foreach($files as $file) {
	$p->addFile($file, basename($file));
}

$p->setStub('<?php
$pharname = basename(__FILE__);

require_once "phar://$pharname/Error.class.php";
require_once "phar://$pharname/Await.class.php";
require_once "phar://$pharname/Async.class.php";
require_once "phar://$pharname/Promise.class.php";
require_once "phar://$pharname/functions.php";

__HALT_COMPILER();');

echo "Phar build !" . PHP_EOL;
echo "Version: " . $init_version . "->" . $composer["version"] . PHP_EOL;
echo "At: " . $timestamp . PHP_EOL;

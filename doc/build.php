<?php

require __DIR__ . "/ReadmeBuilder.class.php";

$md = new ReadmeBuilder();
$md->loadDocumentation(__DIR__ . "/../src");
$md->fromTemplate(__DIR__ . "/template/README.md");

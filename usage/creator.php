<?php
require __DIR__ . '/../vendor/autoload.php';

$buildAssistant = new \Madkom\Docker\Creator\Assistant(__DIR__ . '/build', __DIR__ . '/resources', __DIR__ . '/partials', [
    "phpVersion" => '5'
]);

$buildAssistant->createImage(__DIR__ . '/build/build.sh', 'ci', __DIR__ . '/templates/Dockerfile-cli', 'registry.madkom.pl/php-5.6:cli');
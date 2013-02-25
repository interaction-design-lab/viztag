<?php

require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

$app->get('/', function() {
  return 'viztag v0.0.1 homepage';
});

$app->get('/hello', function() {
  return 'Hello!';
});

$app->run();

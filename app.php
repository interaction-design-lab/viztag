<?php

#################
# CONFIG STUFFS #
#################

error_reporting(E_ALL);
define('DEBUG', 1);

$config = json_decode(file_get_contents('./config.json'));  # app config
require_once __DIR__.'/vendor/autoload.php';  # silex
require_once './util/functions.php';
$dbh = db_connect($config->db);

###################
# THE APPLICATION #
###################

$app = new Silex\Application();

$app->get('/', function() use ($dbh) {
  //debug($dbh);
  return 'viztag v0.0.1';
});

$app->get('/hello', function() {
  return 'Hello!';
});

$app->run();

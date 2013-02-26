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

use Symfony\Component\HttpFoundation\Request;
$app = new Silex\Application();
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app['debug'] = true;

$app->get('/', function() use ($app) {
  $toview = array('help_person' => 'phil');
  return $app['twig']->render('index.twig', $toview);
});

# display the log in form
$app->get('/login', function() use ($app) {
  return $app['twig']->render('login.twig');
});

# log the user out
$app->get('/logout', function() use ($app) {
  $app['session']->set('user', null);
  return $app->redirect('/viztag');
});

# log in a coder with username/password - for now, test/password
$app->post('/login', function(Request $request) use ($app) {
  $username = $request->get('username');
  $password = $request->get('password');
  if ($username == 'test' && $password == 'password') {
    $app['session']->set('user', array('username' => $username));
    return $app->redirect('/viztag');
  } else {  # try again... TODO add error message
    return $app->redirect('/viztag/login');
  }
});

# load a randomly selected status and display tagging / commenting for it
$app->get('/tag', function() use ($app) {
  if (null == $user = $app['session']->get('user')) {
    return $app->redirect('/viztag/login');
  }
  return 'todo';
});

# persist tagging / commenting for a status
$app->post('/tag/{id}', function($id) use ($app) {
  return 'POST tag: todo';
});

$app->run();

<?php

#################
# CONFIG STUFFS #
#################

error_reporting(E_ALL);
define('DEBUG', 1);

require_once __DIR__.'/vendor/autoload.php';  # silex
require_once './util/functions.php';
require_once './config.php';  # app config => $config
$dbh = db_connect($config['db']);

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
  $data = array('help_person' => 'phil');
  return $app['twig']->render('index.twig', $data);
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
  $data = array('key' => 'val');
  return $app['twig']->render('tag.twig', $data);
});

# persist tagging / commenting for a status
$app->post('/tag/{id}', function($id) use ($app) {
  return 'POST tag: todo';
});

$app->run();

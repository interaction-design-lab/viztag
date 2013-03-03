<?php

#################
# CONFIG STUFFS #
#################

error_reporting(E_ALL);
define('DEBUG', 1);

require_once __DIR__.'/vendor/autoload.php';  # silex
require_once './util/functions.php';
require_once './config.php';  # app config loaded into $config
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
$app->get('/tags', function() use ($app, $dbh) {
  $query="SELECT namespace, tag FROM tags";
  $sql=$dbh->prepare($query);
  $sql->execute();
  $results = $sql->fetchAll();
  $data = array();
  foreach($results as $line){
    $data[] = $line[0] . ": " . $line[1];
  }
  $data =  array_values($data);
  return $app->json($data, 200, array('Content-Type' => 'application/json'));
});

$app->run();

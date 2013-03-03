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
$app->get('/tag', function() use ($app, $dbh, $config) {
  if (null == $user = $app['session']->get('user')) {
    return $app->redirect('/viztag/login');
  }
  $sql = 'select * from verastatuses order by rand() limit 1';  # TODO limit to images new to coder
  $query = $dbh->prepare($sql);
  $query->execute();
  $data = array_pop($query->fetchAll(PDO::FETCH_ASSOC));
  $data['src'] = $config['img_base_path'] . $data['image_path'];
  return $app['twig']->render('tag.twig', $data);
});

# persist tagging / commenting for a status
$app->get('/tags', function() use ($app, $dbh) {
  $query='SELECT namespace, tag FROM tags';
  $sql=$dbh->prepare($query);
  $sql->execute();
  $results = $sql->fetchAll(PDO::FETCH_ASSOC);
  $data = array_map('detagify', $results);
  return $app->json($data, 200, array('Content-Type' => 'application/json'));
});

$app->run();

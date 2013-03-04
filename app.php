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

# silex before filter
$app->before(function() use ($app) {

  // push user to views
  $app['twig']->addGlobal('user', $app['session']->get('user'));

  // short-term flash message (e.g. for error/success msgs)
  $flash = $app['session']->get('flash');
  $app['session']->set('flash', null);
  if (!empty($flash)) {
    $flash['type'] = $flash[0];
    $flash['msg'] = $flash[1];
    $app['twig']->addGlobal('flash', $flash);
  }
});

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
$app->post('/login', function(Request $request) use ($app, $dbh) {
  $username = $request->get('username');
  $password = sha1($request->get('password'));

  $query = $dbh->prepare('select * from coders where username=? and password=?');
  $query->execute(array($username, $password));
  $user = $query->fetch(PDO::FETCH_ASSOC);
  if ($user) {
    $app['session']->set('user', array('id' => 2, 'username' => $username));
    $app['session']->set('flash', array('success', 'you are logged in'));
    return $app->redirect('/viztag');
  } else {
    $app['session']->set('flash', array('error', 'username/password incorrect'));
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

# persist this coder's tags for the given verastatus
$app->post('/tag', function (Request $request) use($app, $dbh) {
  if (null == $user = $app['session']->get('user')) {
    $app['session']->set('flash', array('error', 'please log in!'));
    return $app->redirect('/viztag/login');
  }

  $vs_id = $request->get('vs_id');
  $rawtags = rtrim(trim($request->get('tags')), ',');
  $tags = array_map('detagify', explode(',', $rawtags));

  $tag_lookup = $dbh->prepare('select * from tags where namespace=:namespace and tag=:tag');
  $tag_insert = $dbh->prepare('insert into tags (namespace, tag) values (:namespace, :tag');
  $tagging_insert = $dbh->prepare('insert into tags_verastatuses (tag_id, coder_id, verastatus_id) values (:tag_id, :coder_id, :vs_id)');

  foreach ($tags as $tag) {

    // get the tag_id, TODO inserting a new tag if needed
    $tag_lookup->execute(array(':namespace' => $tag['namespace'],
                               ':tag' => $tag['tag']));
    $t = $tag_lookup->fetch(PDO::FETCH_ASSOC);
    $tag['id'] = $t['id'];

    // insert tagging into tags_verastatuses
    $arr = array(':tag_id' => $tag['id'],
                 ':coder_id' => $user['id'],
                 ':vs_id' => $vs_id);
    if (!$tagging_insert->execute($arr)) {
      # TODO figure out best way to handle errors
      //debug($tagging_insert->errorInfo());
    }
  }
  $app['session']->set('flash', array('success', 'taggings saved!'));
  return $app->redirect('/viztag/tag');
});

# persist tagging / commenting for a status
$app->get('/tags', function() use ($app, $dbh) {
  $sql = 'SELECT namespace, tag FROM tags';
  $query = $dbh->prepare($sql);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_ASSOC);
  $data = array_map('tagify', $results);
  return $app->json($data, 200, array('Content-Type' => 'application/json'));
});

# admin view for taggings
$app->get('/taggings', function() use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }

  $sql = <<<SQL
select s.verastatus_id, v.image_path, count(s.tag_id) as num_tags
from tags_verastatuses s, coders u, tags t, verastatuses v
where
	s.coder_id=u.id
	and s.tag_id=t.id
  and s.verastatus_id=v.id
group by verastatus_id
SQL;
  $query = $dbh->prepare($sql);
  $query->execute();
  $ret = $query->fetchAll(PDO::FETCH_ASSOC);
  $taggings = array();
  foreach ($ret as $r) {
    $r['src'] = $config['img_base_path'] . $r['image_path'];
    $taggings[] = $r;
  }
  return $app['twig']->render('taggings.twig', array('taggings'=>$taggings));
});

# admin view for particular tagging
$app->get('/taggings/{id}', function($id) use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }

  $sql = <<<SQL
select s.verastatus_id, s.tag_id, s.coder_id, v.image_path, t.namespace, t.tag
from tags_verastatuses s, coders u, tags t, verastatuses v
where
	s.coder_id=u.id
	and s.tag_id=t.id
  and s.verastatus_id=v.id
  and s.verastatus_id=?
SQL;
  $query = $dbh->prepare($sql);
  $query->execute(array($id));
  $ret = $query->fetchAll(PDO::FETCH_ASSOC);
  $tags = array();
  foreach ($ret as $r) {
    $r['src'] = $config['img_base_path'] . $r['image_path'];
    $tags[] = $r;
  }
  $data = array('verastatus' => $tags[0],
                'tags' => $tags);
  return $app['twig']->render('tagging.twig', $data);
});

$app->run();

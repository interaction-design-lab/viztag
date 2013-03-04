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

# GET:root
$app->get('/', function() use ($app) {
  $data = array('help_person' => 'phil');
  return $app['twig']->render('index.twig', $data);
});

# display the log in form
# GET:login
$app->get('/login', function() use ($app) {
  return $app['twig']->render('login.twig');
});

# log in a coder with username/password - for now, test/password
# POST:login
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

# log the user out
# GET:logout
$app->get('/logout', function() use ($app) {
  $app['session']->set('user', null);
  return $app->redirect('/viztag');
});

# select a viable status at random, and redirect to /tag/{status_id}
# GET:tag
$app->get('/tag', function() use ($app, $dbh, $config) {
  if (null == $user = $app['session']->get('user')) {
    $app['session']->set('flash', array('error', 'Please log in'));
    return $app->redirect('/viztag/login');
  }
  # TODO limit to images new to coder
  $sql = <<<SQL
  select * from
  verastatuses
  order by rand() limit 1
SQL;
  $query = $dbh->prepare($sql);
  $query->execute();
  $data = array_pop($query->fetchAll(PDO::FETCH_ASSOC));
  return $app->redirect('/viztag/tag/'.$data['id']);
});

# display tagging interface for status with id={id}
# GET:tag/id
$app->get('/tag/{id}', function($id) use($app, $dbh, $config) {

  // get status info
  $query = $dbh->prepare('select * from verastatuses where id=?');
  if (!$query->execute(array($id))) {
    print_r($query->errorInfo());
    return 'Broken...';
  }
  if (null == $status = $query->fetch(PDO::FETCH_ASSOC)) {
    $app['session']->set('flash', array('error', 'Invalid statusID. Please hit \'tag\' again...'));
    return $app->redirect('/viztag');
  }

  # add src to status
  $status['src'] = $config['img_base_path'] . $status['image_path'];

  # get tags
  $data = array('tags' => getTags($dbh),
                'status' => $status);
  return $app['twig']->render('tag.twig', $data);
});

# persist this coder's tags for the given verastatus
# POST:tag
$app->post('/tag', function (Request $request) use($app, $dbh) {
  if (null == $user = $app['session']->get('user')) {
    $app['session']->set('flash', array('error', 'please log in!'));
    return $app->redirect('/viztag/login');
  }
  $vs_id = $request->get('vs_id');
  $tags = array_flip(array_filter(array_flip($request->request->all()),
                     'isTagParam'));
  if (in_array(-1, array_values($tags))) {
    $app['session']->set('flash', array('error', 'Please select a tag for each namespace...'));
    return $app->redirect('/viztag/tag/' . $vs_id);
  }

  $tag_insert = $dbh->prepare('insert into tags_verastatuses (tag_id, coder_id, verastatus_id) values (:tag_id, :coder_id, :vs_id)');

  foreach ($tags as $tag => $tag_id) {

    // insert tagging into tags_verastatuses
    $arr = array(':tag_id' => $tag_id,
                 ':coder_id' => $user['id'],
                 ':vs_id' => $vs_id);
    if (!$tag_insert->execute($arr)) {
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

function getTags($dbh) {
  $sql = 'select * from tags';
  $query = $dbh->prepare($sql);
  $query->execute();
  $tags = array();
  foreach($query->fetchAll(PDO::FETCH_ASSOC) as $tag) {
    $tags[$tag['namespace']][] = $tag;
  }
  return $tags;
}

function isTagParam($x) {
  return substr($x, 0, 4) === 'tag-';
}

$app->run();

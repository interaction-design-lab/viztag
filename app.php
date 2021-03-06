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
    $app['session']->set('user', $user);
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

#$app->get('/tag', function() {
#  return 'ha';
#});

# select a viable status at random, and redirect to /tag/{status_id}
# GET:agreement
$app->get('/agreement', function() use ($app, $dbh, $config) {
  if (null == $user = $app['session']->get('user')) {
    $app['session']->set('flash', array('error', 'Please log in'));
    return $app->redirect('/viztag/login');
  }
  # limit to images new to coder, in the right sample AGREEMENT1
  $sql = "select * from verastatuses s where dataset='vera-wellness-2011' and sample='AGREEMENT1' and s.id not in (select distinct verastatus_id from tags_verastatuses v where coder_id=".$user['id'].") order by rand() limit 1";
  $query = $dbh->prepare($sql);
  $query->execute();
  $data = array_pop($query->fetchAll(PDO::FETCH_ASSOC));
  if (empty($data)) {
    return "agreement sample complete";
  } else {
    return $app->redirect('/viztag/tag/'.$data['id']);
  }
});

# select a viable status at random, and redirect to /tag/{status_id}
# for agreement sample with users 
# GET:tag
$app->get('/tag', function() use ($app, $dbh, $config) {
  if (null == $user = $app['session']->get('user')) {
    $app['session']->set('flash', array('error', 'Please log in'));
    return $app->redirect('/viztag/login');
  }
  # limit to images new to coder
  $sql = "select * from verastatuses s where dataset='vera-wellness-2011' and s.id not in (select distinct verastatus_id from tags_verastatuses v where coder_id=".$user['id'].") order by rand() limit 1";
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
  #$status['src'] = $config['img_base_path'] . $status['image_path'];
  $status['src'] = $status['image_path'];

  # get tags
  $data = array('tags' => getTags($dbh),
                'status' => $status);
  return $app['twig']->render('tag.twig', $data);
});

# persist this coder's tags for the given verastatus
# POST:tag
$app->post('/tag', function(Request $request) use($app, $dbh) {
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

  // insert comment (if any)
  $comment = $request->get('comment');
  if (!empty($comment)) {
    $comment_insert = $dbh->prepare('insert into comments (verastatus_id, coder_id, comment) values (?, ?, ?)');
    if (!$comment_insert->execute(array($vs_id, $user['id'], $comment))) {
      //debug($comment_insert->errorInfo());
    }
  }

  $app['session']->set('flash', array('success', 'taggings saved!'));

  // fix hack! by passing start page param along?
  if ($user['id'] == 5 || $user['id'] == 6) {
    return $app->redirect('/viztag/agreement');
  } else {
    return $app->redirect('/viztag/tag');
  }
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
# GET:taggings
$app->get('/taggings', function() use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }
  return $app['twig']->render('taggings.twig');
});

# admin view for taggings
# GET:taggings/by-image
$app->get('/taggings/by-image', function() use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }

  $sql = <<<SQL
select verastatus_id as status_id, image_path, count(coder_id) as num_coders
from
(select s.verastatus_id, v.image_path, coder_id
from tags_verastatuses s, coders u, tags t, verastatuses v
where
	s.coder_id=u.id
	and s.tag_id=t.id
  and s.verastatus_id=v.id
group by verastatus_id, coder_id) tmp
group by verastatus_id
SQL;
  $query = $dbh->prepare($sql);
  $query->execute();
  $ret = $query->fetchAll(PDO::FETCH_ASSOC);
  $taggings = array();
  foreach ($ret as $r) {
    #$r['src'] = $config['img_base_path'] . $r['image_path'];
    $r['src'] = $r['image_path'];
    $taggings[] = $r;
  }
  return $app['twig']->render('by-image.twig', array('taggings'=>$taggings));
});

# admin view for tag mismatches
$app->get('/taggings/mismatch', function() use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }

  $tags = getTags($dbh);
  $sql = 'select * from tags_verastatuses t, verastatuses v where t.verastatus_id=v.id and (t.coder_id=3 or t.coder_id=4)';
  $query = $dbh->prepare($sql);
  $query->execute();
  $ret = $query->fetchAll(PDO::FETCH_ASSOC);

  // sort taggings by v.id
  $taggings = array();
  foreach ($ret as $r) {
    $taggings[$r['verastatus_id']][] = $r;
  }

  // loop thru taggings, keeping those showing a mismatch
  foreach ($taggings as $t) {
    debug($t);
    exit();
  }


  $mm = array();
  foreach ($ret as $r) {
    #$r['src'] = $config['img_base_path'] . $r['image_path'];
    $r['src'] = $r['image_path'];
    $mm[$r['status_id']][] = $r;
  }
  $data = array('mismatches' => $mm);
  return $app['twig']->render('mismatch.twig', $data);
});

# admin view for particular tagging
$app->get('/taggings/by-image/{id}', function($id) use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }

  $sql = <<<SQL
select s.verastatus_id, s.tag_id, s.coder_id, u.username, v.image_path, t.namespace, t.tag
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
    #$r['src'] = $config['img_base_path'] . $r['image_path'];
    $r['src'] = $r['image_path'];
    $tags[$r['username']][] = $r;
  }
  $data = array('status' => $r,
                'tags' => $tags);
  return $app['twig']->render('by-image-single.twig', $data);
});

# admin view for sorting by tag
$app->get('/taggings/by-tags/{tags}', function($tags) use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }
  $data = array('tags' => getTags($dbh));
  if($tags != ''){
    $data['images'] = getImages($tags,$dbh,$config);
  }
  
  return $app['twig']->render('by-tags.twig',$data);
})->value('tags', '');

# admin view for sorting by tag
$app->post('/taggings/by-tags', function(Request $request) use ($app, $dbh, $config) {
  $user = $app['session']->get('user');
  if (null == $user || $user['username'] != 'admin') {
    $app['session']->set('flash', array('error', 'admin only!'));
    return $app->redirect('/viztag');
  }
  $tags = $request->get('tag');
  if($tags == NULL){
    $app['session']->set('flash', array('error', "Must select at least one tag"));
    return $app->redirect('/viztag/taggings/by-tags');
  }
  return $app->redirect('/viztag/taggings/by-tags/'.implode("_",$tags));
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

function getImages($tags,$dbh,$config){
  $tags = implode(" OR tag_id=", explode("_", $tags));
  $sql = <<<SQL
SELECT image_path, id, tag_id FROM tags_verastatuses v 
JOIN verastatuses s on s.id=v.verastatus_id
GROUP BY id
HAVING tag_id = $tags
ORDER BY RAND()
SQL;
  debug($sql);
  $query = $dbh->prepare($sql);
  $query->execute();
  $ret = $query->fetchAll(PDO::FETCH_ASSOC);
  $pics = array();
  foreach ($ret as $r) {
    #$r['src'] = $config['img_base_path'] . $r['image_path'];
    $r['src'] = $r['image_path'];
    $pics[] = $r;
  }
  
  return $pics;
  
}

function isTagParam($x) {
  return substr($x, 0, 4) === 'tag-';
}

$app->run();

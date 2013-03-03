<?php

#// times and timezones
#define('DATETIME_FORMAT', 'Y-m-d H:i:s');
#$utc = new DateTimeZone('UTC');
#$now = new DateTime('now', $utc);
#$now_s = $now->format(DATETIME_FORMAT);

// connect to db
function db_connect($db) {
  try {
      $dbh = new PDO('mysql:host='.$db['host'].';dbname='.$db['name'], $db['user'], $db['pass']);
  } catch (PDOException $e) {
      echo $e->getMessage();
      die();
  }
  return $dbh;
}

// print out contents of a var, with optional description
function debug($thing, $description=null) {
    if (DEBUG >= 1) {
        if ($description != null) {
            echo "> $description:\n";
        }
        if (is_array($thing)) {
            print_r($thing);
        } else {
            var_dump($thing);
        }
        echo "\n";
    }
}

// given a dict keys 'namespace' and 'tag',
// return a string <namespace>:<tag>
function detagify($arr) {
  return $arr['namespace'].':'.$arr['tag'];
}

function stripslashes_deep($val) {
  $val = is_array($val) ?
    array_map('stripslashes_deep', $val) :
    stripslashes($val);
  return $val;
}

?>

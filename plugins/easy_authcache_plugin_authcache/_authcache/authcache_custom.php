<?php

// If authcache applied patch to call method switch
// http://drupal.org/node/916266
if (function_exists('authcache_get_global_array')) {
    $source = authcache_get_global_array();
} else {
    $source = $_GET;
}
// Remove from request easy authcache data (else some code like pager adds it to links)
unset($_REQUEST['easy_authcache']);

// Get url from we have ajax request
$url = $source['current_url'];
// Remove current url from global arrays as it is unnessary
unset($source['current_url']);
unset($_REQUEST['current_url']);
// Parse getted current url and set q and other values to global array
// to make drupal think that current request was from that url
$url_parts = parse_url($url);
$_SERVER['REQUEST_URI'] = preg_replace("/[-a-zA-Z0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-z]{2,4}\)?/i", '', $url);
$_GET['q'] = ltrim(urldecode($url_parts['path']), '/');
$params = array();
if(isset($url_parts['query'])) {
  foreach (explode('&', $url_parts['query']) as $param) {
    $key_value = explode('=', $param);
    $params[$key_value[0]] = urldecode($key_value[1]);
  }
}

// For some links that use destination we should back to current page
if (isset($params['destination'])) {
    $destination = ltrim($_GET['q'], '/') .'?'. drupal_authcache_query_string_encode($params);
    $_REQUEST['destination'] = urlencode($destination);
}
$_GET = array_merge($_GET, $params);

include_once './includes/common.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL); // Use FULL if needed for additional functions
//Include easy authcache ajax process code
module_load_include('inc', 'easy_authcache_plugin_authcache', 'callback/authcache.ajax');


/**
 * Function  creates url query from array
 *
 * @param array $query query items
 * @param array $exclude query items to exclude
 * @param string $parent parent of query items
 * @return string url query
 */
function drupal_authcache_query_string_encode($query, $exclude = array(), $parent = '') {
  $params = array();

  foreach ($query as $key => $value) {
    $key = rawurlencode($key);
    if ($parent) {
      $key = $parent .'['. $key .']';
    }

    if (in_array($key, $exclude)) {
      continue;
    }

    if (is_array($value)) {
      $params[] =  drupal_authcache_query_string_encode($value, $exclude, $key);
    }
    else {
      $params[] = $key .'='. rawurlencode($value);
    }
  }

  return implode('&', $params);
}

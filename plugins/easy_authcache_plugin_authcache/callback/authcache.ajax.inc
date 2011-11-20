<?php
// $Id$

/**
 * @file
 * File provide callback for ajax request
 */


/**
 * Implementation of some kind of "hook" provided by authcache
 * @param mix $data data passed from js. Neccasery for callback
 * @return string json result
 */
function _authcache_easy_authcache($data) {
  return easy_authcache_plugin_authcache_process($data);
}
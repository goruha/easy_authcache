<?php
/**
 * Created by JetBrains PhpStorm.
 * User: goruha
 * Date: 9/9/12
 * Time: 1:48 AM
 * To change this template use File | Settings | File Templates.
 */

function template_preprocess_easy_authcache_vcl_generator_vcl_3_eac(&$vars) {
  $urls = explode("\n", $vars['ulrs_path']);
  $urls = array_map('trim', $urls);
  $output = array();
  foreach ($urls as $url) {
    $output[] = "req.url ~ \"$url\"";
  }
  $vars['ulrs_path'] = implode("\n      || ", $output);
}

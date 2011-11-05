<?php
// $Id$

/**
 * @file
 * File contains admin form code provided by authcache ui module
 */

define('EASY_AUTHCACHE_UI_CONFIG_HOOK', 'easy_authcache_config');

function easy_authcache_ui_admin_form($form_state) {
  $form = array();

  foreach (module_implements(EASY_AUTHCACHE_UI_CONFIG_HOOK) as $module) {
    $form += module_invoke($module, EASY_AUTHCACHE_UI_CONFIG_HOOK);
  }

  return system_settings_form($form);
}

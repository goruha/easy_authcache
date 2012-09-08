<?php
/**
 * Created by JetBrains PhpStorm.
 * User: goruha
 * Date: 9/8/12
 * Time: 10:49 PM
 * To change this template use File | Settings | File Templates.
 */


/**
 * Form for vcl generation
 *
 * @return array
 */
function easy_authcache_vcl_generator_form() {
  $form = array();

  $varnish_versions_supported = array(
    EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_VERSION_2_1 => EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_VERSION_2_1,
    EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_VERSION_3_X => EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_VERSION_3_X
  );

  $form['easy_authcache_vcl_varnish_version'] = array (
    '#type' => 'radios',
    '#title' => t('Varnish version'),
    '#description' => t('Select varnish version you use'),
    '#default_value' => easy_authcache_vcl_varnish_version(),
    '#options' => $varnish_versions_supported,
  );

  $form['easy_authcache_vcl_cookies_to_pass'] = array(
    '#type' => 'textfield',
    '#title' => t('Cookies to pass'),
    '#description' => t('List of cookies that will be passed to backend'),
    '#default_value' => easy_authcache_vcl_cookies_to_pass()
  );

  $form['easy_authcache_vcl_urls_to_pass'] = array(
    '#type' => 'textarea',
    '#title' => t('Urls to pass'),
    '#description' => t('List of urls that will be passed to backend'),
    '#default_value' => easy_authcache_vcl_urls_to_pass(),
  );

  $form['easy_authcache_vcl_http_auth'] = array(
    '#type' => 'fieldset',
    '#title' => t('Http authentication'),
    '#description' => t('Configs for http authentication'),
  );

  $form['easy_authcache_vcl_http_auth']['easy_authcache_vcl_use_http_auth'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use http auth'),
    '#description' => t('Use http auth'),
    '#default_value' => variable_get('easy_authcache_vcl_use_http_auth', FALSE)
  );

  $form['easy_authcache_vcl_http_auth']['easy_authcache_vcl_use_http_auth_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Http authentication name'),
    '#description' => t('Http authentication name'),
    '#default_value' => variable_get('easy_authcache_vcl_use_http_auth_name', '')
  );

  $form['easy_authcache_vcl_http_auth']['easy_authcache_vcl_use_http_auth_pass'] = array(
    '#type' => 'textfield',
    '#title' => t('Http authentication password'),
    '#description' => t('Http authentication password'),
    '#default_value' => variable_get('easy_authcache_vcl_use_http_auth_pass', '')
  );

  $form['easy_authcache_vcl_http_auth']['easy_authcache_vcl_http_auth_denied_html'] = array(
    '#type' => 'textarea',
    '#title' => t('Not authorized html'),
    '#description' => t('Html that will be shown on wrong http auth error.'),
    '#default_value' => easy_authcache_vcl_http_auth_denied_html(),
  );


  $page_ttl_disabled = easy_authcache_plugin_varnish_page_cache_rule() == EASY_AUTHCACHE_PLUGIN_VARNISH_CACHE_NONE;

  $form['easy_authcache_vcl_ttl'] = array(
    '#type' => 'fieldset',
    '#title' => t('Page ttl'),
    '#description' => t('Configs for page ttl'),
    '#disabled' => $page_ttl_disabled,
  );

  $form['easy_authcache_vcl_ttl']['easy_authcache_vcl_ttl'] = array(
    '#type' => 'textfield',
    '#title' => t('Page ttl'),
    '#description' => t('Page ttl'),
    '#default_value' => easy_authcache_vcl_ttl(),
    '#disabled' => $page_ttl_disabled,
  );

  $form['easy_authcache_vcl_ttl']['easy_authcache_vcl_ttl_unit'] = array(
    '#type' => 'select',
    '#title' => t('Page ttl unit'),
    '#description' => t('Page ttl unit'),
    '#options' => _easy_authcache_vcl_generator_ttl_units(),
    '#default_value' => easy_authcache_vcl_ttl_unit(),
    '#disabled' => $page_ttl_disabled,
  );

  $esi_ttl_disabled = easy_authcache_plugin_varnish_esi_cache_rule() == EASY_AUTHCACHE_PLUGIN_VARNISH_CACHE_NONE;

  $form['easy_authcache_vcl_esi_ttl'] = array(
    '#type' => 'fieldset',
    '#title' => t('Page ttl'),
    '#description' => t('Configs for page ttl'),
    '#disabled' => $esi_ttl_disabled,
  );

  $form['easy_authcache_vcl_esi_ttl']['easy_authcache_vcl_esi_ttl'] = array(
    '#type' => 'textfield',
    '#title' => t('ESI ttl'),
    '#description' => t('ESI ttl'),
    '#default_value' => easy_authcache_vcl_esi_ttl(),
    '#disabled' => $esi_ttl_disabled,
  );

  $form['easy_authcache_vcl_esi_ttl']['easy_authcache_vcl_esi_ttl_unit'] = array(
    '#type' => 'select',
    '#title' => t('ESI ttl unit'),
    '#description' => t('ESI ttl unit'),
    '#options' => _easy_authcache_vcl_generator_ttl_units(),
    '#default_value' => easy_authcache_vcl_esi_ttl_unit(),
    '#disabled' => $esi_ttl_disabled,
  );

  $form['easy_authcache_vcl_files_hide'] = array(
    '#type' => 'textfield',
    '#title' => t('Files to hide'),
    '#description' => t('List of files extentions to hide'),
    '#default_value' => easy_authcache_vcl_files_hide()
  );

  $form['easy_authcache_vcl_files_show'] = array(
    '#type' => 'textfield',
    '#title' => t('Files to show'),
    '#description' => t('List of files extentions to show allways'),
    '#default_value' => easy_authcache_vcl_files_show()
  );

  $form['easy_authcache_vcl_files_cache'] = array(
    '#type' => 'fieldset',
    '#title' => t('Files to cache'),
    '#description' => t('Files extentions to cache'),
  );

  $form['easy_authcache_vcl_files_cache']['easy_authcache_vcl_files_cache_list'] = array(
    '#type' => 'textfield',
    '#title' => t('Files to cache'),
    '#description' => t('List of files extentions to cache'),
    '#default_value' => easy_authcache_vcl_files_cache_list()
  );

  $form['easy_authcache_vcl_files_cache']['easy_authcache_vcl_files_cache_action'] = array(
    '#type' => 'select',
    '#title' => t('Action for files to cache'),
    '#description' => t('Action for files to cache'),
    '#options' => array(
      EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_ACTION_CACHE => t('Cache'),
      EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_ACTION_PASS => t('Pass'),
      EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_ACTION_PIPE => t('Pipe'),
    ),
    '#default_value' => easy_authcache_vcl_files_cache_action()
  );

  $form['easy_authcache_vcl_service_ips'] = array(
    '#type' => 'textarea',
    '#title' => t('Service ip list'),
    '#description' => t('List of ip that have access to service urls.'),
    '#default_value' => easy_authcache_vcl_service_ips()
  );

  $form['easy_authcache_vcl_backend_error_html'] = array(
    '#type' => 'textarea',
    '#title' => t('Backend error html'),
    '#description' => t('Html that will be shown on backend error.'),
    '#default_value' => easy_authcache_vcl_backend_error_html(),
  );


  $form['easy_authcache_vcl_bad_request_html'] = array(
    '#type' => 'textarea',
    '#title' => t('Bad request html'),
    '#description' => t('Html that will be shown on bad request.'),
    '#default_value' => easy_authcache_vcl_bad_request_html(),
  );

  $form = system_settings_form($form);
  return $form;
}

function easy_authcache_vcl_generate() {
  $items['base_vcl'] = array(
    '#type' => 'textarea',
    '#default_value' => easy_authcache_vcl_generate_base_vcl(),
    '#title' => t('Base VCL'),
    '#disabled' => TRUE,
  );

  $items['eac_vcl'] = array(
    '#type' => 'textarea',
    '#default_value' => easy_authcache_vcl_generate_eac_vcl(),
    '#title' => t('Easy authcache VCL'),
    '#disabled' => TRUE,
  );

  return $items;
}

function easy_authcache_vcl_get($type) {
  $content = '';
  switch ($type) {
    case 'default':
      $content = easy_authcache_vcl_generate_base_vcl();
      break;

    case 'eac':
      $content = easy_authcache_vcl_generate_eac_vcl();
      break;
  }
  if ($content) {
    $headers = array(
      'Content-Type: ' . 'application/octet-stream',
      'Content-Length: ' . strlen($content),
      'Content-Disposition: inline; filename="' . $type . '.vcl"',
      'Content-Transfer-Encoding: binary',
      'Accept-Ranges: bytes',
      'Cache-Control: private',
      'Pragma: private',
    );
    foreach ($headers as $header) {
      drupal_set_header($header);
    }
    print $content;
    die();
  }
}

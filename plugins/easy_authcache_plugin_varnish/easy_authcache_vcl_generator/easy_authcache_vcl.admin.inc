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

  $form['varnish_version'] = array (
    '#type' => 'radios',
    '#title' => t('Varnish version'),
    '#description' => t('Select varnish version you use'),
    '#default_value' => EASY_AUTHCACHE_VCL_GENERATOR_VARNISH_VERSION_3_X,
    '#options' => $varnish_versions_supported,
  );



  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

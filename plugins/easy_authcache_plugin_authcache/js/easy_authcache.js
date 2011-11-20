// $Id$

/**
 * @file
 * Provide JS to make easy authcache ajax
 *
 */


/**
 * Function that will call ajax
 * It should be run once that is why we do not use behaviours
 * @param data
 */

$(function() {
    // Send current page url
    var url = window.location.href;
    var ajaxJson = {
        'current_url': url
    };
    // Send max_age if setted
    if (authcacheIsNumeric(Drupal.settings.easy_authcache.max_age)) {
        ajaxJson['max_age'] = Drupal.settings.easy_authcache.max_age;
    }

    // If single request settings is setted
    if (Drupal.settings.easy_authcache.single_request == true) {
        var send = [];
        send['easy_authcache'] = JSON.stringify(Drupal.settings.easy_authcache.items);
        ajaxJson = $.extend(ajaxJson, send);
        if (typeof(Authcache) != 'undefined') Authcache.ajaxRequest(ajaxJson);
    }
    else {
        // Call one request per one easyauthcache plugin
        for (var key in Drupal.settings.easy_authcache.items) {
            var send = [];
            var temp = {};
            temp[key] = Drupal.settings.easy_authcache.items[key];
            send['easy_authcache'] = JSON.stringify(temp);
            ajaxJson = $.extend(ajaxJson, send);
            if (typeof(Authcache) != 'undefined') Authcache.ajaxRequest(ajaxJson);
        }
    }
});


/**
 * JS callback function that will be called by authcache
 * Function intergrate ajax result into current page
 *
 * @param data
 */
function _authcache_easy_authcache(data) {
    var data_obj = eval("(" + data + ");");
    // Merge js settings
    for (key1 in data_obj.js) {
        authcacheMergeSettings(data_obj.js[key1]['setting']);
    }

    // include js files
    for (key1 in data_obj.js) {
        for (key2 in data_obj.js[key1]) {
            if (key2 != 'inline' && key2 != 'setting') {
                authcacheAddJsFiles(data_obj.js[key1][key2]);
            }
        }
    }

    // insert content
    for (key in data_obj.items) {
        var context = '';
        var type = data_obj.items[key].type;
        if (type == 'text') {
            // insert result as simple text
            context = data_obj.items[key].output;
        }
        else {
            // insert result as html
            context = $('<' + type + '>' + data_obj.items[key].output + '</' + type + '>');
        }
        // Insert content into page
        $(_easy_authchache_selector(key)).replaceWith(context);
        if (type != 'text') {
            // Apply behaviours for html inserted result
            Drupal.attachBehaviors(context);
        }
    }
}

function _easy_authchache_selector(hash) {
  return '.dynamic-region-' + hash;
}

/**
 * Merge Drupal js settings with settings on current page
 * @param array settings Drupal settings to merge with settings on page
 */
function authcacheMergeSettings(settings) {
    for (key in settings) {
        Drupal.settings = authcacheArrayMergeRecursive(settings[key], Drupal.settings);
    }
}

/**
 * Function adds unexisited js files on page
 *
 * @param array js_files list of js files to add. Files allready exists on page will be skipped
 */
function authcacheAddJsFiles(js_files) {
    // Make ajax sync to prevent event races
    $.ajaxSetup({async: false});
    // Store behaviours that we have before getting new js files
    var old_behaviours = new Array();
    for (key in Drupal.behaviors) {
        old_behaviours[key] = key;
    }

    for (key in js_files) {
        var js_exists = authcacheInArray(key, Drupal.settings.easy_authcache_js);
        // If we have not this file load it
        if (js_exists == false) {
            $.getScript('/' + key);
            Drupal.settings.easy_authcache_js[Drupal.settings.easy_authcache_js.length] = key;
        }
    }
    // Store behaviours that we have after getting new js files
    var new_behaviours = new Array();
    for (key in Drupal.behaviors) {
        new_behaviours[key] = key;
    }

    // Get new behaviours that are differs between old and new ones
    var need_to_run = authcacheArrayDiffKey(new_behaviours, old_behaviours);
    // Apply new behaviours to hole document (emulate document ready event)
    for (func in need_to_run) {
        eval("Drupal.behaviors." + func + "(document)");
    }
    // Return ajax to async mode
    $.ajaxSetup({async: true});
}

/**
 * Merge check if there is value in array. Ecvivalent to php function in_array
 * @param string needle
 * @param array haystack
 * @param bool argStrict if type strict check
 * @return bool
 */
function authcacheInArray(needle, haystack, argStrict) {
    var key = '', strict = !!argStrict;

    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Merge array recursivly. Ecvivalent to php function array_merge_recursive
 * @param array arr1
 * @param array arr2
 * @return array
 */
function authcacheArrayMergeRecursive(arr1, arr2) {
    var idx = '';
    if ((arr1 && (arr1 instanceof Array)) && (arr2 && (arr2 instanceof Array))) {
        for (idx in arr2) {
            arr1.push(arr2[idx]);
        }
    } else if ((arr1 && (arr1 instanceof Object)) && (arr2 && (arr2 instanceof Object))) {
        for (idx in arr2) {
            if (idx in arr1) {
                if (typeof arr1[idx] == 'object' && typeof arr2 == 'object') {
                    arr1[idx] = authcacheArrayMergeRecursive(arr1[idx], arr2[idx]);
                } else {
                    arr1[idx] = arr2[idx];
                }
            } else {
                arr1[idx] = arr2[idx];
            }
        }
    }
    return arr1;
}

/**
 * Returns the entries of arr1 that have keys which are not present in any of the others arguments.
 * Ecvivalent to php function array_diff_key
 */
function authcacheArrayDiffKey() {
    var arr1 = arguments[0], retArr = {};
    var k1 = '', i = 1, k = '', arr = {};

    arr1keys:    for (k1 in arr1) {
        for (i = 1; i < arguments.length; i++) {
            arr = arguments[i];
            for (k in arr) {
                if (k === k1) {                    // If it reaches here, it was found in at least one array, so try next value
                    continue arr1keys;
                }
            }
            retArr[k1] = arr1[k1];
        }
    }
    return retArr;
}

/**
 * Returns true if value is a number or a numeric string
 *
 */
function authcacheIsNumeric(mixed_var) {
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}

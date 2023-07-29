<?php

/**
 * Plugin Name:       Scribo Comptage de vue
 * Plugin URI:        https://www.corbisier.fr/wordpress/plugins/scribo-comptage-vue/
 * Description:       Un plugin pour compter le nombre du vue par jour, par pc et ip
 * Version:           1.0.01
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            Eric CORBISIER
 * Author URI:        https://www.corbisier.fr/wordpress/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://www.corbisier.fr/wordpress/
 * Text Domain:       scribo-comptage
 * Domain Path:       /languages
 */

defined('ABSPATH') or die();
define('WPSCV_DIR_PLUGIN', 'scribo-comptage_vue');
define('WPSCV_VERSION', '1.0.01');

$args_version = array(
    WPSCV_DIR_PLUGIN => WPSCV_VERSION,
    "css" => "1.0.01",
    "js" => "1.0.01"
);



// absolue : 

define('WPSCV_URI_FILE', __FILE__);                                       //C:\wamp64\www\wordpress\wp-content\plugins\scribo-adminbar\scribo-mapping.php
define('WPSCV_URI_BASENAME', plugin_basename(WPSCV_URI_FILE));         // scribo-adminbar/scribo-mapping.php
define('WPSCV_URI_NAME', trim(dirname(WPSCV_URI_BASENAME), '/')); // scribo-mapping
define('WPSCV_URI', untrailingslashit(dirname(WPSCV_URI_FILE)));          //C:\wamp64\www\wordpress\wp-content\plugins\scribo-mapping
define('WPSCV_URI_INC', WPSCV_URI . '/inc');
define('WPSCV_URI_ADMIN', WPSCV_URI . '/admin');

// relative : 

define('WPSCV_URL', plugin_dir_url(__FILE__));
define('WPSCV_URL_INC', WPSCV_URL . '/inc');
define('WPSCV_URL_ADMIN', WPSCV_URL . '/admin');
define('WPSCV_URL_CSS', WPSCV_URL . '/assets/css');
define('WPSCV_URL_JS', WPSCV_URL . '/assets/JS');

require_once WPSCV_URI . '/load.php';
require_once WPSCV_URI . '/counter.php';
require_once WPSCV_URI . '/exclude.php';
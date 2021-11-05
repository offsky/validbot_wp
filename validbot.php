<?php
/**
 * Plugin Name:       ValidBot
 * Plugin URI:        https://www.validbot.com/wordpress-plugin.php
 * Description:       100-Point Inspection and Validation of Your Domain Name and Website
 * Tags:              validate, checkup, SEO, inspection, inspect, best practices, web developer, page speed
 * Version:           1.0.0
 * Stable tag:        1.0.0
 * Requires at least: 5.8
 * Tested up to:      5.8.1
 * Requires PHP:      7.0
 * Author:            ValidBot
 * Author URI:        https://www.validbot.com/
 * Donate Link:       https://www.validbot.com/subscribe.php
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

//Prevent this file from being called directly instead of through WP framework
defined('WPINC') or die;

//For debugging
// define('WP_DEBUG', true);
// define('SAVEQUERIES', true);

//create the classes
define('VALIDBOT_VERSION','1.0.0');
require plugin_dir_path(__FILE__).'includes/ValidBot_Base.php';
require plugin_dir_path(__FILE__).'includes/ValidBot_Admin.php';

$ValidBot_Base = new ValidBot_Base();
$ValidBot_Admin = new ValidBot_Admin();

//register lifecycle hooks
register_activation_hook(__FILE__, array($ValidBot_Base,'activate'));
register_deactivation_hook(__FILE__, array($ValidBot_Base,'deactivate'));
register_uninstall_hook(__FILE__, array($ValidBot_Base,'uninstall'));
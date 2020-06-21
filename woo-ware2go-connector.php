<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * @package Woo-Ware2Go
 */
/*
Plugin Name: Woo Ware2Go API Importer
Plugin URI: http://saniulahsan.info
Description: This plugins checks the WooCommerce status of an order and pushes the data to Ware2GO API when the order is ready for a success. Can be applied for amy third party plugin
Version: 1.0
Author: Saniul Ahsan
Author URI: http://saniulahsan.info
Text Domain: woo-ware2go
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    die('No script kiddies please!');
}

define('WOO_WARE2GO_PLUGIN_DIR', plugin_dir_path(__FILE__));

// function to create the DB / Options / Defaults
function bpax_trigger_activating_plugin()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ware2go_api_logs_bpax';
    // create the ECPT metabox database table
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `time` timestamp NOT NULL DEFAULT current_timestamp(),
            `response` text DEFAULT NULL,
            `api` varchar(255) DEFAULT NULL,
            `method` varchar(255) DEFAULT NULL,
            `data` text DEFAULT NULL,
            `order_id` bigint(20) DEFAULT NULL,
            `status` int(11) DEFAULT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Delete the table when uninstalling the plugin
function bpax_trigger_deactivating_plugin()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ware2go_api_logs_bpax';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'bpax_trigger_activating_plugin');

// run the uninstall scripts upon the plugin deactivation
register_deactivation_hook(__FILE__, 'bpax_trigger_deactivating_plugin');

require_once(WOO_WARE2GO_PLUGIN_DIR . '/class/bootfile.class.php');

BpaxAddFile::addFiles('/', 'helpers', 'php');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    BpaxAddFile::addFiles('class', 'api.class', 'php');
    BpaxAddFile::addFiles('class', 'trackorder.class', 'php');
    BpaxAddFile::addFiles('views', 'settings', 'php');
    BpaxAddFile::addFiles('views', 'logs', 'php');

    add_action('admin_menu', 'bpax_woo_ware2go_settings');
    function bpax_woo_ware2go_settings()
    {
        add_menu_page('Woo Ware2Go Connector', 'BPX Ware2Go', 'manage_options', 'bpx-ware2go-api-settings', 'bpax_woo_ware2go_settings_details', BpaxAddFile::addFiles('assets/images', 'icon-small', 'png', true), 100);
        add_submenu_page('bpx-ware2go-api-settings', 'Api logs', 'Api logs', 'manage_options', 'bpx-ware2go-api-logs', 'bpax_wp_tracker_logs_details');
    }

} else {
    add_action('admin_notices', 'bpax_error_message');
}

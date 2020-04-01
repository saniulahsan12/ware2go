<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
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
if ( !function_exists( 'add_action' ) ) {
	die( 'No script kiddies please!' );
}

define( 'WOO_WARE2GO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAIL_PDF_DIR', ABSPATH . 'wp-content/uploads/ware2go_upload_files/' );

require_once( WOO_WARE2GO_PLUGIN_DIR . '/class/bootfile.class.php' );

AddFile::addFiles('/', 'helpers', 'php');

if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    AddFile::addFiles('class', 'api.class', 'php');
    AddFile::addFiles('class', 'trackorder.class', 'php');
    AddFile::addFiles('views', 'settings', 'php');
    AddFile::addFiles('views', 'logs', 'php');

    add_action('admin_menu', 'woo_ware2go_settings');
    function woo_ware2go_settings()
    {
        add_menu_page('Woo Ware2Go Connector', 'BPX Ware2Go', 'manage_options', 'bpx-ware2go-api-settings', 'woo_ware2go_settings_details', AddFile::addFiles('assets/images', 'icon-small', 'png', true), 100);
        add_submenu_page('bpx-ware2go-api-settings', 'Api logs', 'Api logs', 'manage_options', 'bpx-ware2go-api-logs', 'wp_tracker_logs_details');
    }

} else {
    add_action('admin_notices', 'error_message');
}

<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: Scholar Scrapper
 * Description: A plugin for scraping Google Scholar articles.
 * Version: 1.0
 * Author: Guillaume ELAMBERT <guillaume.elambert@yahoo.fr>
 * Author URI: https://elambert-guillau.me
 * Text Domain: scholar-scrapper
 */

defined('ABSPATH') || exit;

const PLUGIN_PATH =  __DIR__ . "/";
const PLUGIN_SLUG = "scholar_scrapper";


const PYTHON_PATH = "/Users/guillaume/.pyenv/shims/pythons";

// TODO : CLEAN THIS FILE

require_once __DIR__ . "/src/index.php";

if(defined("SUPER_DEBUG")) {
	var_dump(SUPER_DEBUG);
}


// Add the shortcode to the plugin
add_shortcode('scholar_scrapper', 'scholar_scrapper');

register_activation_hook(__FILE__, 'scholar_scrapper_activation');
add_action('admin_init', 'scholar_scrapper_admin_init');
add_action( 'admin_menu', 'add_scholar_scrapper_menu' );


function scholar_scrapper_activation() {
	// Add the settings to the database
	//scholar_scrapper_register_fields_settings();
	//scholar_scrapper_default_settings();

	// Add the cron job
	// TODO: Do the cron stuff here
	//scholar_scrapper_add_cron_job();
}



/**
 * Displays the settings page if the user has the correct permissions.
 */
function scholar_scrapper_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    scholar_scrapper_display_settings_form();
    // Include the settings form view
    #include_once (PLUGIN_PATH . 'src/SettingsPage/index.php');
    //include_once (PLUGIN_PATH . 'src/Scheduling.php');
}


/**
 * Register the settings.
 */
function scholar_scrapper_admin_init()
{

    scholar_scrapper_register_fields_settings();

    // Enregistrement des options de paramètres
//    register_setting(PLUGIN_PAGE, SETTINGS_NAMES['CRON_FREQUENCY']);
//    register_setting(PLUGIN_PAGE, SETTINGS_NAMES['PYTHON_PATH']);
}


/**
 * Adds the settings page to the admin menu.
 */
function add_scholar_scrapper_menu() {
    // add_submenu_page( 'Scholar Scrapper', 'Scholar Scrapper', 'manage_options', 'scholar-scrapper', PLUGIN_PAGE );
    //add_settings_section( PLUGIN_NAME, 'Scholar Scrapper', PLUGIN_PAGE, 'general' );
    //add_options_page( 'Scholar Scrapper', 'Scholar Scrapper', 'manage_options', PLUGIN_NAME, PLUGIN_SLUG );
	$icon = file_get_contents(PLUGIN_PATH . 'assets/img/google-scholar.svg');

	add_menu_page(
		'Scholar Scrapper',
		'Scholar Scrapper',
		'manage_options',
		PLUGIN_SLUG, 'scholar_scrapper_settings_page',
		'data:image/svg+xml;base64,' . base64_encode( $icon ),
		100
	);
}


/**
 * Add settings link to plugin actions
 *
 * @param  array  $plugin_actions
 * @param  string $plugin_file
 * @since  1.0
 * @return array
 */
function add_plugin_link( $plugin_actions, $plugin_file ) {

    $new_actions = array();

    if( strpos( $plugin_file, basename(__FILE__) ) ) {


        $new_actions['cl_settings'] = sprintf( __( '<a href="%s">Settings</a>', PLUGIN_SLUG ), esc_url( admin_url( sprintf('options-general.php?page=%s', PLUGIN_SLUG ) ) ) );
    }

    return array_merge( $new_actions, $plugin_actions );
}
add_filter( 'plugin_action_links', 'add_plugin_link', 10, 2 );
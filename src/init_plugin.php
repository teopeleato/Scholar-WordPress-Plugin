<?php

use Model\ScholarPublication;

// Add the shortcode to the plugin
add_shortcode( 'scholar_scraper', 'scholar_scraper_display_result' );
//add_shortcode( 'scholar_scraper', 'scholar_scraper_start_scraping' );

// Add the action to activate the plugin
register_activation_hook( PLUGIN_FILE, 'scholar_scraper_activation' );

// Handle deactivation
register_deactivation_hook( PLUGIN_FILE, 'scholar_scraper_deactivation' );

// Add the action to init the plugin
add_action( 'admin_init', 'scholar_scraper_admin_init' );

// Add the action to create the menu
add_action( 'admin_menu', 'add_scholar_scraper_menu' );

// Add the action to add links in the "Plugins" page of WordPress
add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'scholar_scraper_add_plugin_links', 10, 4 );

// Add the action to call when the settings are updated
add_action( 'update_option', 'scholar_scraper_on_settings_update', 10, 3 );

// Action triggered when an error occurs
add_action( 'admin_notices', 'scholar_scraper_admin_notices' );

// Add the action to call for initializing the block
add_action( 'enqueue_block_editor_assets', 'scholar_scraper_custom_block_script_register' );
add_action( 'init', 'scholar_scraper_custom_block_script_register' );


/**
 * Fonction qui gère les actions à effectuer pour initialiser le plugin.
 *
 * @param string|null $cronFrequency La fréquence de l'exécution du cron. Si null, on utilise la valeur enregistrée en BDD ou celle par défaut si aucune entrée en BDD.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_init_everything( string $cronFrequency = null ) {

	// Init the styles
	scholar_scraper_init_styles();

	// Init the settings
	scholar_scraper_register_fields_settings();

	// On met à jour la tâche cron
	scholar_scraper_update_schedule_event( $cronFrequency );
}


/**
 * Fonction qui gère les actions à effectuer pour initialiser des styles.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_init_styles() {
	wp_register_style( 'scholar_scraper_dashicons', PLUGIN_URL . 'assets/css/scholar-scraper-font/css/scholar-scraper.css' );
	wp_register_style( 'scholar_scraper_result_page_style', PLUGIN_URL . 'assets/css/scholar-scraper-result-page.css' );

	wp_enqueue_style( 'scholar_scraper_dashicons' );
	wp_enqueue_style( 'scholar_scraper_result_page_style' );
}


/**
 * Fonction qui gère les actions à effectuer lors de l'activation du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_activation() {
	scholar_scraper_init_everything();
	wp_schedule_single_event( time(), CRON_HOOK_NAME );
}


/**
 * Fonction qui gère les actions à effectuer lors de la désactivation du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_deactivation() {
	scholar_scraper_unschedule_event( CRON_HOOK_NAME );
}


/**
 * Adds the settings page to the admin menu.
 * @return void
 * @since  1.0.0
 */
function add_scholar_scraper_menu() {

	add_menu_page(
		'Scholar Scraper',
		'Scholar Scraper',
		'manage_options',
		PLUGIN_SLUG, 'scholar_scraper_display_settings_page',
		PLUGIN_ICON,
		100
	);
}


/**
 * Adds items to the plugin's action links on the Plugins listing screen.
 *
 * @param array<string,string> $plugin_actions Array of action links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @param mixed[] $plugin_data An array of plugin data.
 * @param string $context The plugin context.
 *
 * @return array<string,string> Array of action links.
 * @since  1.0.0
 */
function scholar_scraper_add_plugin_links( $plugin_actions, $plugin_file, $plugin_data, $context ) {

	$new_actions = array();

	// Entrée : le plugin parcouru n'est pas le plugin Google Scholar Scraper
	if ( strpos( $plugin_file, plugin_basename( PLUGIN_FILE ) ) === false ) {
		return $plugin_actions;
	}

	$new_actions['cl_settings'] = sprintf(
		'<a href="%s">Settings</a>',
		esc_url(
			admin_url(
				sprintf(
					'options-general.php?page=%s', PLUGIN_SLUG )
			)
		)
	);

	return array_merge( $new_actions, $plugin_actions );
}


/**
 * Register the settings.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_admin_init() {
	scholar_scraper_init_everything();
	//scholar_scraper_load_plugin();
}


/**
 * Displays the settings page if the user has the correct permissions.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	scholar_scraper_display_settings_form();
}


/**
 * Fonction qui gère les actions à effectuer lors de la mise à jour d'une option.
 *
 * @param $option string Le nom de l'option mise à jour.
 * @param $old_value mixed La valeur de l'option avant la mise à jour.
 * @param $new_value mixed La nouvelle valeur de l'option.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_on_settings_update( string $option, mixed $old_value, mixed $new_value ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! scholar_scraper_is_plugin_setting( $option ) ) {
		return;
	}

	if ( $option === scholar_scraper_get_setting_name( 'CRON_FREQUENCY' ) ) {
		scholar_scraper_update_schedule_event( $new_value );
	}
}


/**
 * Méthode pour afficher les messages d'erreur.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_admin_notices(): void {
	settings_errors();
}


/**
 * Fonction qui enregistre un type de block Gutenberg.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_custom_block_script_register() {

	wp_enqueue_script(
		'scholar_scraper_block_script',
		PLUGIN_URL . 'assets/js/scholar_scraper_block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n' ),
		true
	);

	$publications_fields = ScholarPublication::get_non_array_fields();

	$defaultSort = DEFAULT_SORT_FIELD;

	// On vérifie que le tableau contient bien un element dont 'value' est égal à DEFAULT_SORT_FIELD
	if ( ! array_key_exists( DEFAULT_SORT_FIELD, $publications_fields ) ) {
		// Si ce n'est pas le cas, on prend le premier élément du tableau
		$defaultSort = array_key_first( $publications_fields );
	}


	wp_localize_script(
		'scholar_scraper_block_script',
		'js_data',
		array(
			'image_data'                    => PLUGIN_ICON_BASE64,
			'plugin_url'                    => PLUGIN_URL,
			'default_number_papers_to_show' => DEFAULT_NUMBER_OF_PAPERS_TO_SHOW,
			'available_sort_by_fields'      => $publications_fields,
			'default_sort_by_field'         => $defaultSort,
			'default_sort_by_direction'     => DEFAULT_SORT_DIRECTION,
		)
	);

	wp_register_style(
		'scholar_scraper_block_style',
		PLUGIN_URL . 'assets/css/scholar-scraper-block.css',
		array( 'wp-edit-blocks' )
	);

	register_block_type(
		'scholar-scraper/scholar-scraper-block',
		array(
			'editor_script'   => 'scholar_scraper_block_script',
			'editor_style'    => 'scholar_scraper_block_style',
			'render_callback' => 'scholar_scraper_block_render_callback',
		)
	);
}


/**
 * Fonction qui gère le rendu du block Gutenberg.
 *
 * @param $attributes array Les attributs du block.
 *
 * @return string Le résultat du shortcode Scholar Scraper.
 * @since 1.0.0
 */
function scholar_scraper_block_render_callback( array $attributes ): string {

	scholar_scraper_init_styles();

	if ( ! is_array( $attributes ) || empty( $attributes ) ) {
		return do_shortcode( "[scholar_scraper]" );
	}

	// Revert order of the array
	$attributes = array_reverse( $attributes );

	$attributesString = "";

	foreach ( $attributes as $key => $value ) {
		if ( $value === null ) {
			continue;
		}

		$attributesString .= sprintf( '%s="%s" ', $key, htmlentities( sanitize_text_field( $value ) ) );
	}

	$attributesString = trim( $attributesString );


	// Return the shortcode output
	return do_shortcode( "[scholar_scraper $attributesString author_name=\"super test\"]" );
}
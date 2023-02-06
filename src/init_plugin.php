<?php


// Add the shortcode to the plugin
use Model\ScholarPublication;

add_shortcode( 'scholar_scraper', 'scholar_scraper_display_result' );
//add_shortcode( 'scholar_scraper', 'scholar_scraper_start_scraping' );

// Add the action to activate the plugin
register_activation_hook( __FILE__, 'scholar_scraper_activation' );

// Add the action to init the plugin
add_action( 'admin_init', 'scholar_scraper_admin_init' );

// Add the action to create the menu
add_action( 'admin_menu', 'add_scholar_scraper_menu' );

// Add the action to add links in the "Plugins" page of WordPress
add_filter( 'plugin_action_links', 'add_plugin_links', 10, 2 );

// Add the action to call when the settings are updated
add_action( 'update_option', 'scholar_scraper_on_settings_update', 10, 3 );

// Action triggered when an error occurs
add_action( 'admin_notices', 'scholar_scraper_admin_notices' );

// Add the action to call for initializing the block
add_action( 'enqueue_block_editor_assets', 'scholar_scraper_custom_block_script_register' );
add_action( 'init', 'scholar_scraper_custom_block_script_register' );


/**
 * Fonction qui gère les actions à effectuer lors de l'initialisation du plugin.
 *
 * @param string|null $cronFrequency La fréquence de l'exécution du cron. Si null, on utilise la valeur enregistrée en BDD ou celle par défaut si aucune entrée en BDD.
 *
 * @return void
 */
function scholar_scraper_init_everything( string $cronFrequency = null ) {

	wp_register_style( 'scholar_scraper_dashicons', PLUGIN_URL . 'assets/css/scholar-scrapper-font/css/scholar-scraper.css' );

	wp_enqueue_style( 'scholar_scraper_dashicons' );

	// Init the settings
	scholar_scraper_register_fields_settings();

	// Install the requirements
	scholar_scraper_install_requirements();

	// On vérifie que les fichiers de résultats existent et sont lisibles
	// Sinon on met à jour la tâche cron pour qu'elle soit exécutée immédiatement
	if ( ! is_file( RESULTS_FILE ) || ! is_readable( RESULTS_FILE )
	     || ! is_file( SERIALIZED_RESULTS_FILE ) || ! is_readable( SERIALIZED_RESULTS_FILE ) ) {
		scholar_scraper_update_schedule_event( 0, time() );
	}

	// On met à jour la tâche cron
	scholar_scraper_update_schedule_event( $cronFrequency );
}


/**
 * Fonction qui gère les actions à effectuer lors de l'activation du plugin.
 * @return void
 */
function scholar_scraper_activation() {
	scholar_scraper_init_everything();
}


/**
 * Adds the settings page to the admin menu.
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
 * Add settings link to plugin actions
 *
 * @param array $plugin_actions
 * @param string $plugin_file
 *
 * @return array
 * @since  1.0
 */
function add_plugin_links( array $plugin_actions, string $plugin_file ): array {

	$new_actions = array();

	// Entrée : le plugin parcouru n'est pas le plugin Google Scholar Scraper
	if ( ! strpos( $plugin_file, basename( __FILE__ ) ) ) {
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
 */
function scholar_scraper_admin_init() {
	scholar_scraper_init_everything();
}


/**
 * Displays the settings page if the user has the correct permissions.
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
 */
function scholar_scraper_admin_notices(): void {
	settings_errors();
}


/**
 * Fonction qui enregistre un type de block Gutenberg.
 * @return void
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
		PLUGIN_URL . 'assets/css/scholar_scraper_block.css',
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
 * @return string
 */
function scholar_scraper_block_render_callback( array $attributes ): string {

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
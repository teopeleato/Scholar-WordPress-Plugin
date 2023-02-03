<?php


// Add the shortcode to the plugin
add_shortcode( 'scholar_scraper', 'scholar_scraper_start_scraping' );

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


/**
 * Fonction qui gère les actions à effectuer lors de l'initialisation du plugin.
 *
 * @param string|null $cronFrequency La fréquence de l'exécution du cron. Si null, on utilise la valeur enregistrée en BDD ou celle par défaut si aucune entrée en BDD.
 *
 * @return void
 */
function scholar_scraper_init_everything( string $cronFrequency = null ) {
	// Init the settings
	scholar_scraper_register_fields_settings();

	// Install the requirements
	scholar_scraper_install_requirements();

	// On met à jour la tâche cron ou on la crée si elle n'existe pas.
	scholar_scraper_update_schedule_event( $cronFrequency );

	/*// Si la fréquence du cron est passée en paramètre, on met à jour la tâche cron
	if(!empty($cronFrequency)) {
		// Update the cron frequency
		scholar_scraper_update_schedule_event($cronFrequency);
	}

	// Schedule the cron
	scholar_scraper_schedule_event();*/
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

	$icon = file_get_contents( PLUGIN_PATH . 'assets/img/google-scholar.svg' );

	add_menu_page(
		'Scholar Scraper',
		'Scholar Scraper',
		'manage_options',
		PLUGIN_SLUG, 'scholar_scraper_display_settings_page',
		'data:image/svg+xml;base64,' . base64_encode( $icon ),
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

	if ( strpos( $plugin_file, basename( __FILE__ ) ) ) {


		$new_actions['cl_settings'] = sprintf(
			'<a href="%s">Settings</a>',
			esc_url(
				admin_url(
					sprintf(
						'options-general.php?page=%s', PLUGIN_SLUG )
				)
			)
		);
	}

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
		//scholar_scraper_update_schedule_event( $new_value );
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
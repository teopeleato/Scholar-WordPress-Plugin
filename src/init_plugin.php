<?php

use Model\ScholarPublication;

// Add the shortcode to the plugin
add_shortcode( 'scholar_scraper', 'scholar_scraper_display_result' );
//add_shortcode( 'scholar_scraper_debug', 'scholar_scraper_start_scraping' );

// Add the action to activate the plugin
register_activation_hook( PLUGIN_FILE, 'scholar_scraper_activation' );

// Handle deactivation
register_deactivation_hook( PLUGIN_FILE, 'scholar_scraper_deactivation' );

// Add the action to init the plugin
add_action( 'admin_init', 'scholar_scraper_admin_init', PLUGIN_PRIORITY );

// Add the action to create the menu
add_action( 'admin_menu', 'add_scholar_scraper_menu', PLUGIN_PRIORITY );

// Add the action to add links in the "Plugins" page of WordPress
add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'scholar_scraper_add_plugin_links', 10, 4 );

// Add the action to call when the settings are updated
add_action( 'update_option', 'scholar_scraper_on_settings_update', PLUGIN_PRIORITY, 3 );

// Add the action to call for initializing the block
add_action( 'enqueue_block_editor_assets', 'scholar_scraper_custom_block_script_register', PLUGIN_PRIORITY );
add_action( 'init', 'scholar_scraper_custom_block_script_register', PLUGIN_PRIORITY );

// Add the ajax callback to search in the papers
add_action( 'wp_ajax_search_in_papers', 'scholar_scraper_search_in_papers' );
add_action( 'wp_ajax_nopriv_search_in_papers', 'scholar_scraper_search_in_papers' );


/**
 * Fonction qui gère les fichiers à inclure pour initialiser le plugin.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_includes() {
	require_once PLUGIN_DIR . 'config.php';
	require_once PLUGIN_DIR . 'src/index.php';
}


/**
 * Fonction qui gère les actions à effectuer pour initialiser le plugin.
 *
 * @param string|null $cronFrequency La fréquence de l'exécution du cron. Si null, on utilise la valeur enregistrée en BDD ou celle par défaut si aucune entrée en BDD.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_init_everything( string $cronFrequency = null ) {

	scholar_scraper_includes();

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
	// Custom font for the plugin's icons
	wp_register_style(
		'scholar_scraper_dashicons',
		PLUGIN_URL . 'assets/css/scholar-scraper-font/css/scholar-scraper.css'
	);
	wp_enqueue_style( 'scholar_scraper_dashicons' );

	// Custom style for the plugin's result page
	wp_register_style(
		'scholar_scraper_result_page_style',
		PLUGIN_URL . 'assets/css/scholar-scraper-result-page.css'
	);
	wp_enqueue_style( 'scholar_scraper_result_page_style' );


	// Custom style for the plugin's search form in the result page
	wp_register_style(
		'scholar-scraper-search-form-style',
		PLUGIN_URL . 'assets/css/scholar-scraper-search-form.css'
	);

	wp_enqueue_style( 'scholar-scraper-search-form-style' );
}


/**
 * Fonction qui gère les actions à effectuer lors de l'activation du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_activation() {
	scholar_scraper_init_everything();
	wp_schedule_single_event( time(), CRON_HOOK_IMMEDIATE_NAME );
}


/**
 * Fonction qui gère les actions à effectuer lors de la désactivation du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_deactivation() {
	scholar_scraper_includes();
	scholar_scraper_unschedule_event();
	scholar_scraper_unschedule_event( CRON_HOOK_IMMEDIATE_NAME );
}


/**
 * Adds the settings page to the admin menu.
 * @return void
 * @since 1.0.0
 */
function add_scholar_scraper_menu() {
	scholar_scraper_includes();

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
 * @since 1.0.0
 */
function scholar_scraper_add_plugin_links( array $plugin_actions, string $plugin_file, array $plugin_data, string $context ): array {
	scholar_scraper_includes();

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

	if ( ! current_user_can( 'manage_options' ) && ( ! wp_doing_ajax() ) ) {
		wp_die( __( 'You are not allowed to access this part of the site' ) );
	}

	scholar_scraper_init_everything();
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

	wp_register_style( 'scholar_scraper_settings_page_style', PLUGIN_URL . 'assets/css/scholar-scraper-settings-page.css' );
	// Enqueue the style "scholar_scraper_settings_page.css"
	wp_enqueue_style( 'scholar_scraper_settings_page_style' );

	wp_enqueue_script( 'scholar_scraper_settings_page_script', PLUGIN_URL . 'assets/js/scholar-scraper-settings-page.js' );

	wp_enqueue_script( 'scholar_scraper_settings_page_script' );


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

	scholar_scraper_includes();

	if ( ! scholar_scraper_is_plugin_setting( $option ) ) {
		return;
	}

	if ( $option === scholar_scraper_get_setting_name( 'CRON_FREQUENCY' ) ) {
		scholar_scraper_update_schedule_event( $new_value );
	}
}


/**
 * Fonction qui enregistre un type de block Gutenberg.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_custom_block_script_register() {

	scholar_scraper_includes();

	wp_enqueue_script(
		'scholar_scraper_block_script',
		PLUGIN_URL . 'assets/js/scholar-scraper-block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n' ),
		true
	);

	$publications_fields = ScholarPublication::get_non_array_fields();

	$defaultSort = DEFAULT_PAPERS_SORT_FIELD;

	// On vérifie que le tableau contient bien un element dont 'value' est égal à DEFAULT_PAPERS_SORT_FIELD
	if ( ! array_key_exists( DEFAULT_PAPERS_SORT_FIELD, $publications_fields ) ) {
		// Si ce n'est pas le cas, on prend le premier élément du tableau
		$defaultSort = array_key_first( $publications_fields );
	}

	// Create an array where the keys are the keys of PAPERS_DISPLAY_TYPES and the values are the PAPERS_DISPLAY_TYPES[key][name] values
	$display_types = array_combine(
		array_keys( PAPERS_DISPLAY_TYPES ),
		array_column( PAPERS_DISPLAY_TYPES, 'name' )
	);


	wp_localize_script(
		'scholar_scraper_block_script',
		'js_data',
		array(
			'image_data'                    => PLUGIN_ICON_BASE64,
			'plugin_url'                    => PLUGIN_URL,
			'default_number_papers_to_show' => DEFAULT_NUMBER_OF_PAPERS_TO_SHOW,
			'available_sort_by_fields'      => $publications_fields,
			'default_sort_by_field'         => $defaultSort,
			'default_sort_by_direction'     => DEFAULT_PAPERS_SORT_DIRECTION,
			'available_display_types'       => $display_types,
			'default_display_type'          => DEFAULT_PAPERS_DISPLAY_TYPE,
			'default_allow_search'          => DEFAULT_PAPERS_ALLOW_SEARCH,
			'default_block_id'              => uniqid( 'scholar_scraper_block_' ),
			'default_number_lines_abstract' => DEFAULT_NUMBER_LINES_ABSTRACT,
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
	scholar_scraper_includes();
	scholar_scraper_init_styles();

	if ( ! is_array( $attributes ) || empty( $attributes ) ) {
		return do_shortcode( "[scholar_scraper]" );
	}

	// Register a script to handle the AJAX request
	wp_register_script(
		'scholar_scraper_search_form_script',
		PLUGIN_URL . 'assets/js/scholar-scraper-search-form.js',
		array( 'jquery' ),
	);

	// Enqueue the script
	wp_enqueue_script( 'scholar_scraper_search_form_script' );

	// Localize the script with new data
	wp_localize_script(
		'scholar_scraper_search_form_script',
		'js_data',
		array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'block_id'     => $attributes['block_id'],
			'post_id'      => get_the_ID(),
			'search_delay' => SEARCH_DELAY,
		)
	);


	// Revert order of the array
	$attributes = array_reverse( $attributes );

	if ( isset( $attributes['className'] ) ) {
		unset( $attributes['className'] );
	}
	$attributesString = "";

	foreach ( $attributes as $key => $value ) {
		if ( $value === null ) {
			continue;
		}

		$isString = is_string( $value );
		// Make value a string using json_encode
		$value = json_encode( $value );

		// Entrée : la valeur était déjà une chaîne de caractères avant json_encode
		//       => On supprime les deux premiers et deux derniers caractères (les guillemets)
		if ( $isString ) {
			$value = substr( $value, 1, - 1 );
		}

		// Make sure the value is compatible with HTML attributes
		$value = str_replace( '"', '\\"', $value );

		$attributesString .= sprintf( '%s="%s" ', $key, htmlentities( sanitize_text_field( $value ) ) );
	}

	$attributesString = trim( $attributesString );

	// Return the shortcode output
	return do_shortcode( "[scholar_scraper $attributesString]" );
}

/**
 * Fonction permettant de récupérer les publications correspondant à la requête de recherche (au format HTML).
 * @since 1.1.0
 */
function scholar_scraper_search_in_papers() {
	$query = $_POST['search_query'];
	$query = sanitize_text_field( $query );
	$query = trim( $query );

	if ( empty( $_POST['block_id'] ) || empty( $_POST['post_id'] ) ) {
		wp_send_json_error();
	}

	$bloc_id = $_POST['block_id'];
	$post_id = $_POST['post_id'];


	// Get the content of the current post
	$content = get_post( $post_id )->post_content;


	if ( ! has_blocks( $content ) ) {
		wp_send_json_error( "No blocks found." );
	}

	$blocks = parse_blocks( $content );

	// Find the block with the given id
	$blocks = array_filter( $blocks, function ( $block ) use ( $bloc_id ) {
		// Check that the block is a scholar_scraper block
		if ( $block['blockName'] !== 'scholar-scraper/scholar-scraper-block' ) {
			return false;
		}

		return $block['attrs']['block_id'] === $bloc_id;
	} );

	if ( empty( $blocks ) ) {
		wp_send_json_error( "Block not found." );
		//wp_send_json_error();
	}

	$attrs = array_values( $blocks )[0]['attrs'];

	// On vérifie que la recherche est autorisée
	if ( ( ! isset( $attrs['allow_search'] ) && ! DEFAULT_PAPERS_ALLOW_SEARCH ) || $attrs['allow_search'] === false ) {
		wp_send_json_error( "Search not allowed." );
	}

	$attrs['search_query'] = $query;
	$attrs['is_ajax']      = true;

	wp_send_json_success( scholar_scraper_block_render_callback( $attrs ) );

	// Get the number of papers to show
}
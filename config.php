<?php
/**
 * Utilisée pour vérifier si le plugin est configuré.
 * @since 1.0.0
 */
define( 'SCHOLAR_SCRAPER_VERSION', '1.0' );


/**
 * URL vers le dossier du plugin.
 * @since 1.0.0
 */
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );


/**
 * Nom du plugin.
 * @since 1.0.0
 */
define( 'PLUGIN_NAME', 'Scholar Scraper' );


/**
 * Slug du plugin.
 * @since 1.0.0
 */
define( 'PLUGIN_SLUG', str_replace( ' ', '_', strtolower( PLUGIN_NAME ) ) );


/**
 * Icône du plugin en base 64.
 * @since 1.0.0
 */
define( 'PLUGIN_ICON_BASE64', 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( PLUGIN_DIR . 'assets/img/google-scholar.svg' ) ) );

/**
 * Icône du plugin, acronyme faisant référence à la Dashicons introduite par le plugin dans WordPress.
 * @since 1.0.0
 */
define( 'PLUGIN_ICON', 'dashicons-google-scholar' );


/**
 * Nom des paramètres du plugin en base de données.
 * @since 1.0.0
 */
define( 'OPTION_GROUP', PLUGIN_SLUG . '_settings' );


/**
 * Define how many papers are displayed by default.
 * @since 1.0.0
 */
define( 'DEFAULT_NUMBER_OF_PAPERS_TO_SHOW', 10 );


/**
 * Default field to sort papers by.
 * @since 1.0.0
 */
define( 'DEFAULT_PAPERS_SORT_FIELD', 'pub_year' );

/**
 * Default sort order.
 * @since 1.0.0
 */
define( 'DEFAULT_PAPERS_SORT_DIRECTION', 'desc' );


/**
 * The possible values for the display type of papers.
 * @since 1.1.0
 */
define( 'PAPERS_DISPLAY_TYPES', [
	'list' => [
		'template-file'         => 'PublicationListTemplate.php',
		'name'                  => 'List',
		'container-class'       => 'list',
		'number-lines-abstract' => 0,
	],
	'card' => [
		'template-file'         => 'PublicationCardTemplate.php',
		'name'                  => 'Card',
		'container-class'       => 'card',
		'number-lines-abstract' => 6,
	],

] );


/**
 * The default number of lines of the abstract to display depending on the display type of papers.
 * @since 1.2.0
 */
define( 'DEFAULT_NUMBER_LINES_ABSTRACT', array_combine(
	array_keys( PAPERS_DISPLAY_TYPES ),
	array_column( PAPERS_DISPLAY_TYPES, 'number-lines-abstract' )
) );


/**
 * The default display type of papers.
 * @since 1.1.0
 */
define( 'DEFAULT_PAPERS_DISPLAY_TYPE', array_key_first( PAPERS_DISPLAY_TYPES ) );


/**
 * The default value for the "allow search papers" option.
 * @since 1.1.0
 */
define( 'DEFAULT_PAPERS_ALLOW_SEARCH', true );


/**
 * The time in milliseconds to wait before sending a request when searching for papers.
 * @since 1.1.0
 */
define( 'SEARCH_DELAY', 500 );

/**
 * Fréquences de cron personnalisées.
 * @since 1.0.0
 */
define( 'CUSTOM_CRON_FREQUENCIES',
	[
		/*
		 '1min'  => [
			'interval' => MINUTE_IN_SECONDS,
			'display'  => 'Every minute',
		],
		'5min'  => [
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => 'Every 5 minutes',
		],
		'10min' => [
			'interval' => 10 * MINUTE_IN_SECONDS,
			'display'  => 'Every 10 minutes',
		],
		'15min' => [
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => 'Every 15 minutes',
		],
		'30min' => [
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display'  => 'Every 30 minutes',
		],
		*/
	]
);


// On s'assure que la fonction get_editable_roles existe
if ( ! function_exists( 'get_editable_roles' ) ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
}

// On récupère les rôles éditables
$roles = get_editable_roles();
$roles = array_reverse(
	array_combine(
		array_keys( $roles ),
		array_map(
			static function ( $role ) {
				return translate_user_role( $role['name'] );
			},
			$roles
		)
	)
);


// On s'assure que les intervals de cron personnalisés sont bien enregistrés
if ( ! function_exists( 'scholar_scraper_add_custom_cron_intervals' ) ) {
	require_once PLUGIN_DIR . 'src/Scheduling.php';
}

$cronFrequencies = wp_get_schedules();
// Sort cron frequencies by interval
uasort(
	$cronFrequencies,
	static function ( $a, $b ) {
		return $a['interval'] - $b['interval'];
	}
);
// On récupère les fréquences de cron définies par WordPress
$cronFrequencies = array_combine(
	array_keys( $cronFrequencies ),
	array_map(
	// On récupère les noms affichable des fréquences (valeurs "display" du tableau)
		static function ( $frequency ) {
			return $frequency['display'];
		},
		$cronFrequencies
	)

);


/**
 * Liste des paramètres du plugin.
 * @since 1.0.0
 */
define( 'PLUGIN_SETTINGS',
	[
		'RESEARCHERS_ROLES'   => [
			'name'        => 'researchers_roles',
			'label'       => 'Role of researchers',
			'description' => 'The role(s) of the researchers on the website. This role(s) will be used to determine which users will have their articles retrieved from Google Scholar.',
			'type'        => 'multiselect',
			'options'     => $roles,
		],
		'META_KEY_SCHOLAR_ID' => [
			'name'        => 'meta_key_scholar_id',
			'label'       => 'Metadata key associated to the Google Scholar ID',
			'description' => 'The metadata key used to store the scholar ID of a user.',
			'type'        => 'text',
			'pattern'     => '^.+$',
			'default'     => 'scholar_id',
			'placeholder' => 'Ex: scholar_id',
		],
		'PYTHON_API_THREADS'  => [
			'name'        => 'python_api_threads',
			'label'       => 'Number of threads',
			'description' => 'The number of threads to use when scraping papers.',
			'type'        => 'number',
			'pattern'     => '^[1-9]([0-9])*$',
			'default'     => 10,
			'placeholder' => 'Ex: 10',
			'min'         => 1,
		],
		'CRON_FREQUENCY'      => [
			'name'        => 'cron_frequency',
			'label'       => 'Cron frequency',
			'description' => 'The frequency at which the cron will run.',
			'type'        => 'select',
			'options'     => $cronFrequencies,
		],
		'CRON_RETRY_AFTER'    => [
			'name'        => 'cron_retry_after',
			'label'       => 'Retry interval',
			'description' => 'The number of minutes to wait before retrying to scrape papers that failed to be scraped.',
			'type'        => 'number',
			'pattern'     => '^[1-9]([0-9])*$',
			'default'     => 5,
			'placeholder' => 'Ex: 10',
			'min'         => 5,
		],
		'PYTHON_PATH'         => [
			'name'        => 'python_path',
			'label'       => 'Python Path',
			'description' => 'The path to the Python executable on your server.',
			'type'        => 'text',
			'pattern'     => '^.+$',
			'default'     => '/usr/bin/python3',
			'placeholder' => 'Ex: /usr/bin/python3',
		],
		'PIP_PATH'            => [
			'name'        => 'pip_path',
			'label'       => 'Pip Path',
			'description' => 'The path to the Pip executable on your server.',
			'type'        => 'text',
			'pattern'     => '^.+$',
			'default'     => '/usr/bin/pip3',
			'placeholder' => 'Ex: /usr/bin/pip3',
		],
	]
);


/**
 * Chemin vers le script Python qui permet de récupérer les données de Google Scholar.
 * @since 1.0.0
 */
define( 'PYTHON_SCRIPT_PATH', PLUGIN_DIR . 'assets/python/scholar-python-api.py' );


/**
 * Chemin vers le fichier de dépendances du script Python.
 * @since 1.0.0
 */
define( 'PYTHON_REQUIREMENTS_PATH', PLUGIN_DIR . 'assets/python/requirements.txt' );


/**
 * Nom de l'action qui permet de lancer le script Python.
 * @since 1.0.0
 */
define( 'CRON_HOOK_NAME', 'scholar_scraper_cron_hook' );


/**
 * Nom de l'action qui permet de lancer le script Python immédiatement.
 * @since 1.0.0
 */
define( 'CRON_HOOK_IMMEDIATE_NAME', CRON_HOOK_NAME . '_immediate' );


/**
 * Nom de la transient qui permet de savoir si le cron est en cours d'exécution.
 * @since 1.0.0
 */
define( 'CRON_TRANSIENT', CRON_HOOK_NAME . "scholar_scraper_cron_executing" );


/**
 * Durée de vie du transient qui permet de savoir si le cron est en cours d'exécution (en secondes).
 * @since 1.0.0
 */
define( 'CRON_TRANSIENT_RESET_AFTER', HOUR_IN_SECONDS );


/**
 * Heure de début du cron. (Format: HH:MM:SS)
 * @since 1.0.0
 */
define( 'STARTING_CRON_TIME', '00:00:00' );


/**
 * Fichier de log.
 * @since 1.0.0
 */
define( 'LOG_FILE', PLUGIN_DIR . 'log.txt' );


/**
 * Chemin vers le fichier de résultats.
 * @since 1.0.0
 */
define( 'RESULTS_FILE', PLUGIN_DIR . 'results.json' );


/**
 * Chemin vers le fichier de résultats sérialisé.
 * @since 1.0.0
 */
define( 'SERIALIZED_RESULTS_FILE', PLUGIN_DIR . 'results.ser' );
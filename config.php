<?php
/**
 * Utilisée pour vérifier si le plugin est configuré.
 */
define( 'SCHOLAR_SCRAPPER_VERSION', '1.0' );


/**
 * Chemin vers le dossier du plugin.
 */
define( 'PLUGIN_PATH', __DIR__ . '/' );


/**
 * URL vers le dossier du plugin.
 */
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );


/**
 * Nom du plugin.
 */
define( 'PLUGIN_NAME', 'Scholar Scraper' );


/**
 * Slug du plugin.
 */
define( 'PLUGIN_SLUG', str_replace( ' ', '_', strtolower( PLUGIN_NAME ) ) );


/**
 * Icône du plugin en base 64.
 */
define( 'PLUGIN_ICON_BASE64', 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( PLUGIN_PATH . 'assets/img/google-scholar.svg' ) ) );

/**
 * Icône du plugin, acronyme faisant référence à la Dashicons introduite par le plugin dans WordPress.
 */
define( 'PLUGIN_ICON', 'dashicons-google-scholar' );


/**
 * Nom des paramètres du plugin en base de données.
 */
define( 'OPTION_GROUP', PLUGIN_SLUG . '_settings' );


/**
 * Define how many papers are displayed by default.
 */
define( 'DEFAULT_NUMBER_OF_PAPERS_TO_SHOW', 10 );


/**
 * Default field to sort papers by.
 */
define( 'DEFAULT_SORT_FIELD', 'pub_year' );

/**
 * Default sort order.
 */
define( 'DEFAULT_SORT_DIRECTION', 'desc' );

/**
 * Fréquences de cron personnalisées.
 */
define( 'CUSTOM_CRON_FREQUENCIES',
	[
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
	]
);


// On s'assure que les intervals de cron personnalisés sont bien enregistrés
if ( ! function_exists( 'scholar_scraper_add_custom_cron_intervals' ) ) {
	require_once PLUGIN_PATH . 'src/Scheduling.php';
}


/**
 * Liste des paramètres du plugin.
 */
define( 'PLUGIN_SETTINGS',
	[
		'CRON_FREQUENCY' => [
			'name'    => 'scholar_scraper_cron_frequency',
			'label'   => 'Cron frequency',
			'type'    => 'select',
			'options' =>
			// On récupère les fréquences de cron définies par WordPress
				array_combine(
					array_keys( wp_get_schedules() ),
					array_map(
					// On récupère les noms affichable des fréquences (valeurs "display" du tableau)
						static function ( $frequency ) {
							return $frequency['display'];
						},
						wp_get_schedules()
					)
				),
		],
		'PYTHON_PATH'    => [
			'name'        => 'scholar_scraper_python_path',
			'label'       => 'Python Path',
			'type'        => 'text',
			'pattern'     => '^.+$',
			'default'     => '/usr/bin/python3',
			'placeholder' => 'Ex: /usr/bin/python3',
		],
		'PIP_PATH'       => [
			'name'        => 'scholar_scraper_pip_path',
			'label'       => 'Pip Path',
			'type'        => 'text',
			'pattern'     => '^.+$',
			'default'     => '/usr/bin/pip3',
			'placeholder' => 'Ex: /usr/bin/pip3',
		],
	]
);


/**
 * Chemin vers le script Python qui permet de récupérer les données de Google Scholar.
 */
define( 'PYTHON_SCRIPT_PATH', PLUGIN_PATH . 'ScholarPythonAPI/__init__.py' );


/**
 * Chemin vers le fichier de dépendances du script Python.
 */
define( 'PYTHON_REQUIREMENTS_PATH', PLUGIN_PATH . 'ScholarPythonAPI/requirements.txt' );


/**
 * Nom de l'action qui permet de lancer le script Python.
 */
define( 'CRON_HOOK_NAME', 'scholar_scraper_cron_hook' );


/**
 * Heure de début du cron. (Format: HH:MM:SS)
 */
define( 'STARTING_CRON_TIME', '00:00:00' );


/**
 * Fichier de log.
 */
define( 'LOG_FILE', PLUGIN_PATH . 'log.txt' );


/**
 * Chemin vers le fichier de résultats.
 */
define( 'RESULTS_FILE', PLUGIN_PATH . 'results.json' );


/**
 * Chemin vers le fichier de résultats sérialisé.
 */
define( 'SERIALIZED_RESULTS_FILE', PLUGIN_PATH . 'results.ser' );
<?php
/**
 * Utilisée pour vérifier si le plugin est configuré.
 * @since 1.0.0
 */
define( 'SCHOLAR_SCRAPER_VERSION', '1.0' );


/**
 * Chemin vers le dossier du plugin.
 * @since 1.0.0
 */
define( 'PLUGIN_PATH', __DIR__ . '/' );


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
define( 'PLUGIN_ICON_BASE64', 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( PLUGIN_PATH . 'assets/img/google-scholar.svg' ) ) );

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
define( 'DEFAULT_SORT_FIELD', 'pub_year' );

/**
 * Default sort order.
 * @since 1.0.0
 */
define( 'DEFAULT_SORT_DIRECTION', 'desc' );

/**
 * Fréquences de cron personnalisées.
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
 */
define( 'PYTHON_SCRIPT_PATH', PLUGIN_PATH . 'assets/python/scholar-python-api.py' );


/**
 * Chemin vers le fichier de dépendances du script Python.
 * @since 1.0.0
 */
define( 'PYTHON_REQUIREMENTS_PATH', PLUGIN_PATH . 'assets/python/requirements.txt' );


/**
 * Nom de l'action qui permet de lancer le script Python.
 * @since 1.0.0
 */
define( 'CRON_HOOK_NAME', 'scholar_scraper_cron_hook' );


/**
 * Nom de la transient qui permet de savoir si le cron est en cours d'exécution.
 * @since 1.0.0
 */
define( 'CRON_TRANSIENT', CRON_HOOK_NAME . "scholar_scraper_cron_executing" );


/**
 * Durée de vie de la transient qui permet de savoir si le cron est en cours d'exécution.
 * @since 1.0.0
 */
define( 'CRON_TRANSIENT_RESET_AFTER', MINUTE_IN_SECONDS );


/**
 * Heure de début du cron. (Format: HH:MM:SS)
 * @since 1.0.0
 */
define( 'STARTING_CRON_TIME', '00:00:00' );


/**
 * Fichier de log.
 * @since 1.0.0
 */
define( 'LOG_FILE', PLUGIN_PATH . 'log.txt' );


/**
 * Chemin vers le fichier de résultats.
 * @since 1.0.0
 */
define( 'RESULTS_FILE', PLUGIN_PATH . 'results.json' );


/**
 * Chemin vers le fichier de résultats sérialisé.
 * @since 1.0.0
 */
define( 'SERIALIZED_RESULTS_FILE', PLUGIN_PATH . 'results.ser' );
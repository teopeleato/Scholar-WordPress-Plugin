<?php
/**
 * Nom des paramètres du plugin en base de données.
 */
const OPTION_GROUP = PLUGIN_SLUG . "_settings";


/**
 * Liste des paramètres du plugin.
 */
const SETTINGS = [
	'CRON_FREQUENCY' => [
		'name'    => 'scholar_scrapper_cron_frequency',
		'label'   => 'Cron frequency',
		'type'    => 'select',
		'options' => [
			'hourly'     => 'Hourly',
			'twicedaily' => 'Twice Daily',
			'daily'      => 'Daily',
			'weekly'     => 'Weekly',
			'monthly'    => 'Monthly',
		],
	],
	'PYTHON_PATH'    => [
		'name'    => 'scholar_scrapper_python_path',
		'label'   => 'Python path',
		'type'    => 'text',
		'regex'   => '^.+$',
		'default' => '/usr/bin/python3',
	],
];


/**
 * Enregistre les paramètres du plugin.
 * @return void
 */
function scholar_scrapper_register_fields_settings(): void {
	// On déclare le paramètre "Cron" pour le plugin
	register_setting(
		OPTION_GROUP,
		SETTINGS['CRON_FREQUENCY']['name'],
		'scholar_scrapper_sanitize_cron_frequency'
	);

	// Ajout de la section des paramètres cron
	add_settings_section(
		'section_cron', // ID de la section
		'Cron settings', // Titre de la section
		'', // Callback de la section
		PLUGIN_SLUG // Page des paramètres
	);

	// Ajout du champ de formulaire "Cron" à choix multiple
	add_settings_field(
		SETTINGS['CRON_FREQUENCY']['name'], // ID du champ
		'Cron', // Label du champ
		'scholar_scrapper_display_cron_field', // Callback pour afficher le champ
		PLUGIN_SLUG, // Page des paramètres
		'section_cron' // Section des paramètres
	);


	// On déclare le paramètre "Python Path" pour le plugin
	register_setting(
		OPTION_GROUP,
		SETTINGS['PYTHON_PATH']['name'],
		'scholar_scrapper_sanitize_python_path_field'
	);

	// Ajout de la section de paramètres Python
	add_settings_section(
		'section_python', // ID de la section
		'Python configuration', // Titre de la section
		'', // Callback de la section
		PLUGIN_SLUG // Page des paramètres
	);

	// Ajout du champ de formulaire "Python Path"
	add_settings_field(
		SETTINGS['PYTHON_PATH']['name'], // ID du champ
		'Python Path', // Label du champ
		'scholar_scrapper_display_python_path_field', // Callback pour afficher le champ
		PLUGIN_SLUG, // Page des paramètres
		'section_python' // Section des paramètres
	);

	scholar_scrapper_default_settings();
}


/**
 * Initialise les paramètres du plugin s'ils ne sont pas encore définis.
 *
 * @return void
 */
function scholar_scrapper_default_settings(): void {
	foreach ( SETTINGS as $setting_acronym => $setting ) {
		$value = get_option( $setting['name'], scholar_scrapper_get_default_value( $setting_acronym ) );
		update_option( $setting['name'], $value );
	}
}


/**
 * Récupère la valeur par défaut d'un paramètre.
 * Si le paramètre est de type select, la valeur par défaut est la première option.
 * Sinon, la valeur par défaut est celle définie dans le tableau SETTINGS par le champ 'default'.
 *
 * @param $setting_acronym string Acronyme du paramètre dans le tableau SETTINGS
 *
 * @return mixed|null Valeur par défaut du paramètre ou null si l'acronyme n'existe pas
 */
function scholar_scrapper_get_default_value( string $setting_acronym ): mixed {
	if ( ! isset( SETTINGS[ $setting_acronym ] ) ) {
		return null;
	}

	$setting = SETTINGS[ $setting_acronym ];

	if ( $setting['type'] === 'select' ) {
		return $setting['options'][0];
	}

	return $setting['default'];
}


/**
 * Fonction de nettoyage des données du champ "Cron frequency"
 *
 * @param $input string La valeur du champ saisie par l'utilisateur
 *
 * @return string La valeur du champ nettoyée
 */
function scholar_scrapper_sanitize_cron_frequency( string $input ): string {

	$fieldName = SETTINGS['CRON_FREQUENCY']['name'];

	// Nettoie le champ "cron_frequency"
	if ( ! in_array( $input, array_keys( SETTINGS['CRON_FREQUENCY']['options'] ) ) ) {
		add_settings_error(
			$fieldName,
			esc_attr( 'settings_updated' ),
			"Invalid value for cron frequency : "
		);
		var_dump( $input );

		return get_option( $fieldName, scholar_scrapper_get_default_value( $fieldName ) );
	}

	return $input;
}


/**
 * Fonction de nettoyage du champs "Python Path"
 *
 * @param $input string La valeur du champ saisie par l'utilisateur
 *
 * @return string La valeur du champ nettoyée
 */
function scholar_scrapper_sanitize_python_path_field( string $input ): string {

	$fieldName = SETTINGS['PYTHON_PATH']['name'];

	// Nettoie le champ "python_path"
	if ( empty( $input ) || ! file_exists( $input ) ) {
		add_settings_error(
			$fieldName,
			esc_attr( 'settings_updated' ),
			"Invalid value for python path"
		);

		return get_option( $fieldName, scholar_scrapper_get_default_value( $fieldName ) );
	}

	return sanitize_text_field( $input );
}


/**
 * Méthode pour afficher les messages d'erreur.
 *
 * @return void
 */
add_action( 'admin_notices', 'scholar_scrapper_admin_notices' );
function scholar_scrapper_admin_notices(): void {
	settings_errors();
}
<?php
/**
 * Enregistre les paramètres du plugin.
 * @return void
 */
function scholar_scraper_register_fields_settings(): void {
	// On déclare le paramètre "Cron" pour le plugin
	register_setting(
		OPTION_GROUP,
		PLUGIN_SETTINGS['CRON_FREQUENCY']['name'],
		'scholar_scraper_sanitize_cron_frequency'
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
		PLUGIN_SETTINGS['CRON_FREQUENCY']['name'], // ID du champ
		PLUGIN_SETTINGS['CRON_FREQUENCY']['label'], // Label du champ
		'scholar_scraper_display_cron_field', // Callback pour afficher le champ
		PLUGIN_SLUG, // Page des paramètres
		'section_cron' // Section des paramètres
	);


	// On déclare le paramètre "Python Path" pour le plugin
	register_setting(
		OPTION_GROUP,
		PLUGIN_SETTINGS['PYTHON_PATH']['name'],
		'scholar_scraper_sanitize_python_path_field'
	);

	register_setting(
		OPTION_GROUP,
		PLUGIN_SETTINGS['PIP_PATH']['name'],
		'scholar_scraper_sanitize_pip_path_field'
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
		PLUGIN_SETTINGS['PYTHON_PATH']['name'], // ID du champ
		PLUGIN_SETTINGS['PYTHON_PATH']['label'], // Label du champ
		'scholar_scraper_display_python_path_field', // Callback pour afficher le champ
		PLUGIN_SLUG, // Page des paramètres
		'section_python' // Section des paramètres
	);

	// Ajout du champ de formulaire "Pip Path"
	add_settings_field(
		PLUGIN_SETTINGS['PIP_PATH']['name'], // ID du champ
		PLUGIN_SETTINGS['PIP_PATH']['label'], // Label du champ
		'scholar_scraper_display_pip_path_field', // Callback pour afficher le champ
		PLUGIN_SLUG, // Page des paramètres
		'section_python' // Section des paramètres
	);

	scholar_scraper_default_settings();
}


/**
 * Récupère la valeur par défaut d'un paramètre.
 * Si le paramètre est de type select, la valeur par défaut est la première option.
 * Sinon, la valeur par défaut est celle définie dans le tableau PLUGIN_SETTINGS par le champ 'default'.
 *
 * @param $setting_acronym string Acronyme du paramètre dans le tableau PLUGIN_SETTINGS
 *
 * @return mixed|null Valeur par défaut du paramètre ou null si l'acronyme n'existe pas
 */
function scholar_scraper_get_default_value( string $setting_acronym ): mixed {
	if ( ! isset( PLUGIN_SETTINGS[ $setting_acronym ] ) ) {
		return null;
	}

	$setting = PLUGIN_SETTINGS[ $setting_acronym ];

	if ( $setting['type'] === 'select' ) {
		$keys = array_keys( $setting['options'] );
		if ( ! empty( $keys ) ) {
			return $keys[0];
		}

		return null;
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
function scholar_scraper_sanitize_cron_frequency( string $input ): string {

	$fieldName = PLUGIN_SETTINGS['CRON_FREQUENCY']['name'];

	// Nettoie le champ "cron_frequency"
	if ( ! in_array( $input, array_keys( PLUGIN_SETTINGS['CRON_FREQUENCY']['options'] ) ) ) {
		add_settings_error(
			$fieldName,
			esc_attr( 'settings_updated' ),
			sprintf( 'Invalid value for "%s" : "%s" is not an authorized value.', PLUGIN_SETTINGS['CRON_FREQUENCY']['label'], $input )
		);

		return get_option( $fieldName, scholar_scraper_get_default_value( $fieldName ) );
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
function scholar_scraper_sanitize_python_path_field( string $input ): string {
	return scholar_scraper_sanitize_path_field( 'PYTHON_PATH', $input, true );
}

/**
 * Fonction de nettoyage du champs "Pip Path"
 *
 * @param $input string La valeur du champ saisie par l'utilisateur
 *
 * @return string La valeur du champ nettoyée
 */
function scholar_scraper_sanitize_pip_path_field( string $input ): string {
	return scholar_scraper_sanitize_path_field( 'PIP_PATH', $input, true );
}


/**
 * Fonction générique de nettoyage des champs qui doivent contenir un chemin vers un fichier.
 *
 * @param string $settingAcronym Acronyme du paramètre dans le tableau PLUGIN_SETTINGS.
 * @param string $input La valeur du champ saisie par l'utilisateur.
 *
 * @return string|null La valeur du champ nettoyée. Null si le paramètre n'est pas un champ de formulaire.
 */
function scholar_scraper_sanitize_path_field( string $settingAcronym, string $input, bool $isExecutable = false ): ?string {

	$fieldName = scholar_scraper_get_setting_name( $settingAcronym );

	// Si le paramètre n'est pas un champ de formulaire, on retourne rien
	if ( empty( $fieldName ) ) {
		echo "Erreur : $settingAcronym n'est pas un paramètre de plugin";

		return null;
	}


	$toReturnError = scholar_scraper_get_setting_value( $settingAcronym );

	// Nettoie le champ correspondant à l'acronyme :
	// - Vérifie que la valeur n'est pas vide
	if ( empty( $input ) ) {
		scholar_scraper_add_setting_error( $settingAcronym, 'empty value' );

		return $toReturnError;
	}

	// Replace last DIRECTORY_SEPARATOR by an empty string if it's the last character
	$input = preg_replace( '/' . preg_quote( DIRECTORY_SEPARATOR, '/' ) . '$/', '', $input );


	// Nettoie le champ correspondant à l'acronyme :
	// - Vérifie que le fichier existe (pas un dossier et accessible en lecture)
	if ( ! is_file( $input ) ) {

		// Entrée : Le fichier n'existe pas ou n'est pas accessible (manque de droits)
		if ( ! file_exists( $input ) ) {
			scholar_scraper_add_setting_error(
				$settingAcronym,
				sprintf( '"%s" file does not exist or can not be opened due to restrictions', $input )
			);

			return $toReturnError;
		}

		scholar_scraper_add_setting_error(
			$settingAcronym,
			sprintf( '"%s" is not a file', $input )
		);

		return $toReturnError;
	}


	// Nettoie le champ correspondant à l'acronyme :
	// Si $isExecutable est à true, vérifie que le fichier est exécutable
	if ( $isExecutable && ! is_executable( $input ) ) {
		scholar_scraper_add_setting_error(
			$settingAcronym,
			sprintf( '"%s" file is not executable', $input )
		);

		return $toReturnError;
	}


	//return get_option( $fieldName, scholar_scraper_get_default_value( $fieldName ) );
	return sanitize_text_field( $input );
}


/**
 * Fonction générique pour renvoyer une erreur de validation d'un champ de formulaire.
 *
 * @param string $settingAcronym Acronyme du paramètre dans le tableau PLUGIN_SETTINGS.
 * @param string|null $message Message d'erreur à afficher.
 *
 * @return void
 */
function scholar_scraper_add_setting_error( string $settingAcronym, string $message = null ): void {
	$fieldName = scholar_scraper_get_setting_name( $settingAcronym );

	if ( empty( $fieldName ) ) {
		echo "Erreur : $settingAcronym n'est pas un paramètre de plugin";

		return;
	}

	add_settings_error(
		$fieldName,
		esc_attr( 'settings_updated' ),
		sprintf(
			'%s - Invalid value for "%s"%s.',
			PLUGIN_NAME,
			PLUGIN_SETTINGS[ $settingAcronym ]['label'],
			! empty( $message ) ? " : $message" : '',
		)
	);
}
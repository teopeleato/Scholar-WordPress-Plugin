<?php
/**
 * Enregistre les paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_register_fields_settings(): void {

    // Ensure that the settings are registered only once
    if ( get_option( OPTION_GROUP ) ) {
        return;
    }

    $patternForFieldDescription = '<p class="field-description">%s</p>';

    // On enregistre les paramètres du plugin
    register_setting(
        OPTION_GROUP,
        OPTION_GROUP,
        [
            'type'              => 'array',
            //'default'           => scholar_scraper_get_settings_or_default(),
            'sanitize_callback' => 'scholar_scraper_sanitize_settings',
        ]
    );

    // On met à jour les paramètres par défaut (initialisation en BDD)
    scholar_scraper_set_default_settings();

    $scholarUsers = [];

    foreach ( scholar_scraper_get_setting_value( 'RESEARCHERS_ROLES' ) as $role ) {
        $scholarUsers = array_merge(
            $scholarUsers,
            scholar_scraper_get_list_meta_key( $role, scholar_scraper_get_setting_value( 'META_KEY_SCHOLAR_ID' ) )
        );
    }


    /*
     * Ajout de la section des paramètres du script Python
     */
    add_settings_section(
        'section_scraper', // ID de la section
        'Scholar Scraper settings', // Titre de la section
        'scholar_scraper_display_scholar_scraper_settings_section', // Callback de la section
        PLUGIN_SLUG // Page des paramètres
    );

    $description    = '';
    $settingAcronym = 'RESEARCHERS_ROLES';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_researchers_roles_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_scraper' // Section des paramètres
    );


    $description    = '';
    $settingAcronym = 'META_KEY_SCHOLAR_ID';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_meta_key_scholar_id_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_scraper' // Section des paramètres
    );


    $description    = '';
    $settingAcronym = 'PYTHON_API_THREADS';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_threads_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_scraper' // Section des paramètres
    );


    /*
     * Ajout de la section des paramètres Cron
     */
    add_settings_section(
        'section_cron', // ID de la section
        'Cron settings', // Titre de la section
        'scholar_scraper_display_cron_section', // Callback de la section
        PLUGIN_SLUG // Page des paramètres
    );

    $description    = '';
    $settingAcronym = 'CRON_FREQUENCY';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    // Ajout du champ de formulaire "Cron" à choix multiple
    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_cron_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_cron' // Section des paramètres
    );


    $description    = '';
    $settingAcronym = 'CRON_RETRY_AFTER';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    // Ajout du champ de formulaire "Cron" à choix multiple
    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_retry_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_cron' // Section des paramètres
    );


    /*
     * Ajout de la section des paramètres Python
     */
    add_settings_section(
        'section_python', // ID de la section
        'Python configuration', // Titre de la section
        'scholar_scraper_display_python_section', // Callback de la section
        PLUGIN_SLUG // Page des paramètres
    );

    $description    = '';
    $settingAcronym = 'PYTHON_PATH';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    // Ajout du champ de formulaire "Python Path"
    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_python_path_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_python' // Section des paramètres
    );


    $description    = '';
    $settingAcronym = 'PIP_PATH';

    if ( isset( PLUGIN_SETTINGS[ $settingAcronym ]['description'] ) ) {
        $description = sprintf( $patternForFieldDescription, PLUGIN_SETTINGS[ $settingAcronym ]['description'] );
    }

    // Ajout du champ de formulaire "Pip Path"
    add_settings_field(
        scholar_scraper_get_setting_name( $settingAcronym ), // ID du champ
        PLUGIN_SETTINGS[ $settingAcronym ]['label'] . $description, // Label du champ
        'scholar_scraper_display_pip_path_field', // Callback pour afficher le champ
        PLUGIN_SLUG, // Page des paramètres
        'section_python' // Section des paramètres
    );
}


/**
 * Récupère la valeur par défaut d'un paramètre.
 * Si le paramètre est de type select, la valeur par défaut est la première option.
 * Sinon, la valeur par défaut est celle définie dans le tableau PLUGIN_SETTINGS par le champ 'default'.
 *
 * @param $setting_acronym string Acronyme du paramètre dans le tableau PLUGIN_SETTINGS.
 *
 * @return mixed|null Valeur par défaut du paramètre ou null si l'acronyme n'existe pas.
 * @since 1.0.0
 */
function scholar_scraper_get_default_value( string $setting_acronym ): mixed {
    if ( ! isset( PLUGIN_SETTINGS[ $setting_acronym ] ) ) {
        return null;
    }

    $setting = PLUGIN_SETTINGS[ $setting_acronym ];

    if ( $setting['type'] === 'select' || $setting['type'] === 'multiselect' ) {
        $keys = array_keys( $setting['options'] );
        if ( ! empty( $keys ) ) {

            if ( $setting['type'] === 'multiselect' ) {
                return $keys;
            }

            return $keys[0];
        }

        return null;
    }

    return $setting['default'];
}


/**
 * Fonction de callback pour traiter les données du formulaire de paramètres.
 *
 * @param $input array Données du formulaire.
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return array Données traitées.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_settings( array $input, bool $displayError = true ): array {

    $toReturn = $input;

    // Iterate over the input and sanitize each of the values using "scholar_scraper_sanitize_{nameOfField}" callback
    foreach ( $input as $key => $value ) {
        $sanitize_callback = 'scholar_scraper_sanitize_' . $key;

        // If callback is not set, just leave the input as it is
        if ( ! is_callable( $sanitize_callback ) ) {

            $setting = null;
            if ( isset( PLUGIN_SETTINGS[ $key ] ) ) {
                $setting = PLUGIN_SETTINGS[ $key ];
            } elseif ( isset( PLUGIN_SETTINGS[ strtoupper( $key ) ] ) ) {
                $setting = PLUGIN_SETTINGS[ strtoupper( $key ) ];
            }

            if ( $displayError ) {
                add_settings_error(
                    is_null( $setting ) ? $key : $setting['name'],
                    esc_attr( 'settings_updated' ),
                    sprintf(
                        '%s - The parameter "%s" has not been sanitized.',
                        PLUGIN_NAME,
                        is_null( $setting ) ? $key : $setting['label']
                    )
                );
            }

            // Remove the field from the input
            unset( $toReturn[ $key ] );
            $value = scholar_scraper_get_setting_value( strtoupper( $key ) );
            if ( ! is_null( $value ) ) {
                $toReturn[ $key ] = $value;
            }
            continue;
        }

        // Sanitize the input
        $toReturn[ $key ] = call_user_func( $sanitize_callback, $value, $displayError );
    }

    if ( empty( $toReturn ) && $displayError ) {
        add_settings_error(
            'ALL',
            esc_attr( 'settings_updated' ),
            sprintf(
                '%s - None of the parameters have been sanitized.',
                PLUGIN_NAME,
            )
        );
    }

    return $toReturn;
}


/**
 * Fonction de nettoyage des données du champ "Researchers Role".
 *
 * @param array $input La valeur du champ.
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return array La valeur du champ nettoyée.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_researchers_roles( array $input, bool $displayError = true ): array {

    $errorMessages = [];
    $toReturn      = [];

    if ( empty( $input ) ) {
        scholar_scraper_add_settings_error( 'RESEARCHERS_ROLES', 'Setting is empty' );

        return scholar_scraper_get_setting_value( 'RESEARCHERS_ROLES' );
    }

    $toCompare = array_keys( PLUGIN_SETTINGS['RESEARCHERS_ROLES']['options'] );

    foreach ( $input as $index => $role ) {
        if ( ! in_array( $role, $toCompare ) ) {
            $errorMessages[] = [ 'RESEARCHERS_ROLES', sprintf( '"%s" is not a valid role', $role ) ];
            continue;
        }
        $toReturn[ $role ] = $role;
    }

    if ( empty( $toReturn ) ) {
        $errorMessages[] = [ 'RESEARCHERS_ROLES', 'No accepted values where passed' ];
    }


    if ( ! empty( $errorMessages ) && $displayError ) {

        foreach ( $errorMessages as $errorMessage ) {
            scholar_scraper_add_settings_error( ...$errorMessage );
        }

        return scholar_scraper_get_setting_value( 'RESEARCHERS_ROLES' );
    }

    return array_map( 'sanitize_text_field', $toReturn );
}


/**
 * Fonction de nettoyage des données du champ "Meta key Scholar ID".
 *
 * @param string $input La valeur du champ.
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return string La valeur du champ nettoyée.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_meta_key_scholar_id( string $input, bool $displayError = true ): string {

    $errorMessage = null;

    // Nettoie le champ "Python API threads"
    if ( empty( $input ) ) {
        scholar_scraper_add_settings_error( 'PYTHON_API_THREADS', 'Setting is empty' );

        return scholar_scraper_get_setting_value( 'PYTHON_API_THREADS' );
    }

    return sanitize_text_field( $input );
}


/**
 * Fonction de nettoyage des données du champ "Thread number".
 *
 * @param string $input La valeur du champ.
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return string La valeur du champ nettoyée.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_python_api_threads( string $input, bool $displayError = true ): string {

    $errorMessage = null;

    if ( empty( $input ) ) {
        $errorMessage = 'Setting is empty';
    }

    // Nettoie le champ "Python API threads"
    if ( ! is_numeric( $input ) ) {
        $errorMessage = sprintf( '"%s" is not a number', $input );
    }

    if ( $input < PLUGIN_SETTINGS['PYTHON_API_THREADS']['min'] ) {
        $errorMessage = sprintf( '"%s" must be greater or equal to %s', $input, PLUGIN_SETTINGS['PYTHON_API_THREADS']['min'] );
    }

    if ( ! empty( $errorMessage ) && $displayError ) {
        scholar_scraper_add_settings_error(
            'PYTHON_API_THREADS',
            $errorMessage,
        );

        return scholar_scraper_get_setting_value( 'PYTHON_API_THREADS' );
    }

    return sanitize_text_field( $input );
}


/**
 * Fonction de nettoyage des données du champ "Cron frequency"
 *
 * @param $input string La valeur du champ saisie par l'utilisateur
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return string La valeur du champ nettoyée
 * @since 1.0.0
 */
function scholar_scraper_sanitize_cron_frequency( string $input, bool $displayError = true ): string {

    if ( empty( $input ) ) {
        scholar_scraper_add_settings_error( 'CRON_FREQUENCY', 'Setting is empty' );

        return scholar_scraper_get_setting_value( 'CRON_FREQUENCY' );
    }


    // Nettoie le champ "cron_frequency"
    if ( ! in_array( $input, array_keys( PLUGIN_SETTINGS['CRON_FREQUENCY']['options'] ) ) ) {
        if ( $displayError ) {
            scholar_scraper_add_settings_error(
                'CRON_FREQUENCY',
                sprintf( '"%s" is not an authorized value', $input )
            );
        }

        return scholar_scraper_get_setting_value( 'CRON_FREQUENCY' );
    }

    return sanitize_text_field( $input );
}


/**
 * Fonction de nettoyage des données du champ "Cron retry after"
 *
 * @param $input string La valeur du champ saisie par l'utilisateur
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return string La valeur du champ nettoyée
 * @since 1.0.0
 */
function scholar_scraper_sanitize_cron_retry_after( string $input, bool $displayError = true ): string {

    $errorMessage = null;

    if ( empty( $input ) ) {
        $errorMessage = 'Setting is empty';
    }

    // Nettoie le champ "Python API threads"
    if ( ! is_numeric( $input ) ) {
        $errorMessage = sprintf( '"%s" is not a number', $input );
    }

    if ( $input < PLUGIN_SETTINGS['CRON_RETRY_AFTER']['min'] ) {
        $errorMessage = sprintf( '"%s" must be greater or equal to %s', $input, PLUGIN_SETTINGS['CRON_RETRY_AFTER']['min'] );
    }

    if ( ! empty( $errorMessage ) && $displayError ) {
        scholar_scraper_add_settings_error(
            'CRON_RETRY_AFTER',
            $errorMessage,
        );

        return scholar_scraper_get_setting_value( 'CRON_RETRY_AFTER' );
    }

    return sanitize_text_field( $input );

}


/**
 * Fonction de nettoyage du champs "Python Path".
 *
 * @param $input string La valeur du champ saisie par l'utilisateur.
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return string La valeur du champ nettoyée.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_python_path( string $input, bool $displayError = true ): string {
    return scholar_scraper_sanitize_path_field( 'PYTHON_PATH', $input, $displayError, true );
}


/**
 * Fonction de nettoyage du champs "Pip Path".
 *
 * @param $input string La valeur du champ saisie par l'utilisateur.
 * @param bool $displayError Afficher ou non les erreurs.
 *
 * @return string La valeur du champ nettoyée.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_pip_path( string $input, bool $displayError = true ): string {
    return scholar_scraper_sanitize_path_field( 'PIP_PATH', $input, $displayError, true );
}


/**
 * Fonction générique de nettoyage des champs qui doivent contenir un chemin vers un fichier.
 *
 * @param string $settingAcronym Acronyme du paramètre dans le tableau PLUGIN_SETTINGS.
 * @param string $input La valeur du champ saisie par l'utilisateur.
 * @param bool $displayError Afficher ou non les erreurs.
 * @param bool $isExecutable Le fichier doit-il être exécutable ?
 *
 * @return string|null La valeur du champ nettoyée. Null si le paramètre n'est pas un champ de formulaire.
 * @since 1.0.0
 */
function scholar_scraper_sanitize_path_field( string $settingAcronym, string $input, bool $displayError = true, bool $isExecutable = false ): ?string {

    $fieldName = scholar_scraper_get_setting_name( $settingAcronym );

    // Si le paramètre n'est pas un champ de formulaire, on retourne rien
    if ( empty( $fieldName ) && $displayError ) {

        scholar_scraper_add_settings_error(
            $settingAcronym,
            'not a plugin setting',
        );

        return null;
    }


    $defaultValue = scholar_scraper_get_default_value( $settingAcronym );

    // Nettoie le champ correspondant à l'acronyme :
    // - Vérifie que la valeur n'est pas vide
    if ( empty( $input ) && $displayError ) {
        scholar_scraper_add_settings_error( $settingAcronym, 'empty value' );

        return $defaultValue;
    }

    // Replace last DIRECTORY_SEPARATOR by an empty string if it's the last character
    $input = preg_replace( '/' . preg_quote( DIRECTORY_SEPARATOR, '/' ) . '$/', '', $input );


    // Nettoie le champ correspondant à l'acronyme :
    // - Vérifie que le fichier existe (pas un dossier et accessible en lecture)
    if ( ! file_exists( $input ) ) {

        // Entrée : Le chemin amène à un dossier
        if ( ! is_file( $input ) ) {

            if ( $displayError ) {
                scholar_scraper_add_settings_error(
                    $settingAcronym,
                    sprintf( '"%s" is not a file', $input )
                );
            }

            return $defaultValue;
        }

        if ( $displayError ) {
            scholar_scraper_add_settings_error(
                $settingAcronym,
                sprintf( '"%s" file does not exist or can not be opened due to restrictions', $input )
            );
        }

        return $defaultValue;
    }


    // Nettoie le champ correspondant à l'acronyme :
    // Si $isExecutable est à true, vérifie que le fichier est exécutable
    if ( $isExecutable && ! is_executable( $input ) ) {

        if ( $displayError ) {
            scholar_scraper_add_settings_error(
                $settingAcronym,
                sprintf( '"%s" file is not executable', $input )
            );
        }

        return $defaultValue;

    } elseif ( ! $isExecutable && ! is_readable( $input ) ) {

        if ( $displayError ) {
            scholar_scraper_add_settings_error(
                $settingAcronym,
                sprintf( '"%s" file is not readable', $input )
            );
        }

        return $defaultValue;
    }


    return sanitize_text_field( $input );
}


/**
 * Fonction générique pour renvoyer une erreur de validation d'un champ de formulaire.
 *
 * @param string $settingAcronym Acronyme du paramètre dans le tableau PLUGIN_SETTINGS.
 * @param string|null $message Message d'erreur à afficher.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_add_settings_error( string $settingAcronym, string $message = null ): void {
    $fieldName = scholar_scraper_get_setting_name( $settingAcronym );

    if ( empty( $fieldName ) ) {

        add_settings_error(
            $settingAcronym,
            esc_attr( 'settings_updated' ),
            sprintf(
                '%s - "%s" is not a plugin setting.',
                PLUGIN_NAME,
                $settingAcronym,
            )
        );

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
<?php
/**
 * Initialise les paramètres du plugin s'ils ne sont pas encore définis.
 *
 * @return void
 */
function scholar_scraper_default_settings(): void {
	foreach ( PLUGIN_SETTINGS as $setting_acronym => $setting ) {
		$value = get_option( $setting['name'], scholar_scraper_get_default_value( $setting_acronym ) );
		update_option( $setting['name'], $value );
	}
}


/**
 * Récupère la valeur d'un paramètre.
 *
 * @param string $setting_acronym Acronyme du paramètre.
 *
 * @return string Valeur du paramètre ou valeur par défaut si le paramètre n'est pas défini.
 */
function scholar_scraper_get_setting_value( string $setting_acronym ) {
	return get_option( PLUGIN_SETTINGS[ $setting_acronym ]['name'], scholar_scraper_get_default_value( $setting_acronym ) );
}


/**
 * Récupère le nom d'un paramètre en BDD.
 *
 * @param string $setting_acronym Acronyme du paramètre.
 *
 * @return string|null Nom du paramètre en BDD. Null si l'acronyme n'existe pas dans le tableau PLUGIN_SETTINGS.
 */
function scholar_scraper_get_setting_name( string $setting_acronym ): ?string {
	if ( ! isset( PLUGIN_SETTINGS[ $setting_acronym ] ) ) {
		return null;
	}

	return PLUGIN_SETTINGS[ $setting_acronym ]['name'];
}


/**
 * Récupère les noms de tous les paramètres en BDD.
 *
 * @return array Tableau des noms de tous les paramètres en BDD.
 */
function scholar_scraper_get_settings_names() {
	return array_map( function ( $setting ) {
		return $setting['name'];
	}, PLUGIN_SETTINGS );
}


/**
 * Vérifie si une  chaîne de caractère correspond à l'un des paramètres du plugin.
 *
 * @param string $setting_name La chaîne de caractère à vérifier.
 *
 * @return bool True si la chaîne de caractère correspond à l'un des paramètres du plugin, false sinon.
 */
function scholar_scraper_is_plugin_setting( string $setting_name ) {
	return in_array( $setting_name, scholar_scraper_get_settings_names() );
}


/**
 * Modifie l'heure d'un timestamp.
 *
 * @param string $timestamp Timestamp de la date à modifier
 * @param string $wanted_time Heure au format H:i:s
 *
 * @return string Timestamp modifié au format Y-m-d H:i:s
 */
function scholar_scraper_set_specific_time_timestamp( string $timestamp = "", string $wanted_time = "" ): string {

	// Entrée : Aucun timestamp ou timestamp invalide
	if ( empty( $timestamp ) || ! is_numeric( $timestamp ) ) {
		$timestamp = time();
	}

	// Entrée : Aucune heure
	if ( empty( $wanted_time ) ) {
		$wanted_time = STARTING_CRON_TIME;
	}

	// Entrée : L'heure n'est pas au bon format (H:i:s)
	if ( ! preg_match( '/^([0-1][0-9]|2[0-3])(:[0-5][0-9]){2}$/', $wanted_time ) ) {
		return $timestamp;
	}

	// Conversion du timestamp en date
	$currentDate = DateTime::createFromFormat( 'U', $timestamp );
	// Conversion de l'heure en date
	$wantedTimeDate = DateTime::createFromFormat( 'H:i:s', $wanted_time );

	// Modification de l'heure de la date
	$currentDate->setTime( $wantedTimeDate->format( 'H' ), $wantedTimeDate->format( 'i' ), $wantedTimeDate->format( 's' ) );

	// Conversion de la date en timestamp
	return $currentDate->getTimestamp();
}


/**
 * Fonction permettant de récupérer le prochain timestamp qui match l'heure voulue et l'intervalle.
 *
 * @param string $timestamp Le timestamp de la date actuelle.
 * @param string $wanted_time L'heure voulue au format H:i:s.
 * @param int $interval L'intervalle en secondes.
 *
 * @return string
 */
function scholar_scraper_get_next_specific_timestamp( string $timestamp = "", string $wanted_time = "", int $interval = 0 ): string {
	$timestamp = scholar_scraper_set_specific_time_timestamp( $timestamp, $wanted_time );

	// Si le timestamp est supérieur à la date actuelle, on renvoie le timestamp qui match l'heure voulue
	if ( $timestamp > time() ) {
		return $timestamp;
	}

	if ( $interval == 0 ) {
		return time();
	}

	// Sinon, on trouve le prochain timestamp qui match l'heure voulue et l'intervalle
	$nextTimestamp = $timestamp;

	while ( $nextTimestamp < time() ) {
		$nextTimestamp += $interval;
	}

	return $nextTimestamp;
}


/**
 * Méthode permettant d'afficher un message d'erreur dans le fichier "log.txt".
 *
 * @param string $message Le message d'erreur à afficher.
 *
 * @return void
 */
function scholar_scraper_log( string $message ) {
	if ( empty( $message ) ) {
		return;
	}

	// Check if $message contains PHP_EOL at the end
	if ( substr( $message, - 1 ) != PHP_EOL ) {
		$message .= PHP_EOL;
	}

	# Print the error message in the log.txt file
	$logFile = fopen( PLUGIN_PATH . "log.txt", "a" );
	fwrite( $logFile, $message );
	fclose( $logFile );
}
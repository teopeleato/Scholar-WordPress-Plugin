<?php

use Model\GenericCollection;


/**
 * Class des types de logs.
 */
abstract class LOG_TYPE {
	const INFO = "INFO";
	const WARNING = "WARNING";
	const ERROR = "ERROR";
	const SUCCESS = "SUCCESS";

	const ALL = [
		self::INFO,
		self::WARNING,
		self::ERROR,
		self::SUCCESS
	];
}


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
 * Méthode permettant d'écrire dans un fichier.
 *
 * @param string $filePath Le chemin du fichier.
 * @param string $content Le contenu à écrire.
 * @param bool $append Si true, le contenu sera ajouté à la fin du fichier. Si false, le contenu écrasera le contenu du fichier.
 * @param bool $add_new_line Si true, une nouvelle ligne sera ajoutée à la fin du contenu.
 *
 * @return bool
 */
function scholar_scrapper_write_in_file( string $filePath, string $content, bool $append = true, bool $add_new_line = true ): bool {
	if ( empty( $content ) || empty( $filePath ) ) {
		return false;
	}

	// Check if $message contains PHP_EOL at the end
	if ( $add_new_line && substr( $content, - 1 ) != PHP_EOL ) {
		$content .= PHP_EOL;
	}

	$mode = $append ? "a" : "w";


	# Print the error message in the log.txt file
	$file = fopen( $filePath, $mode );

	// Check if the file has been opened
	if ( $file === false ) {
		return false;
	}

	$toReturn = fwrite( $file, $content );
	fclose( $file );

	// Convert $toReturn to boolean because it could be an integer
	return ( $toReturn !== false );
}


/**
 * Méthode permettant d'afficher un message d'erreur dans le fichier "log.txt".
 *
 * @param string $logType Le type de message.
 * @param string $message Le message d'erreur à afficher.
 *
 * @return bool True si le message a bien été affiché, false sinon.
 */
function scholar_scraper_log( string $logType, string $message ): bool {

	// Entrée : Le message est vide
	//       => On ne crée pas de log
	if( empty( $message ) ) {
		return false;
	}

	// Entrée : Le type de message n'est pas valide
	//       => On ne crée pas de log
	if( ! in_array( $logType, LOG_TYPE::ALL ) ) {
		return false;
	}

	// On récupère la longueur maximale des types de messages
	$maxLength =  max( array_map( 'strlen', LOG_TYPE::ALL ) ) + 3;
	// On ajoute le timestamp et le type de message au message
	$message = sprintf( "%s\t%-{$maxLength}s ", date( "Y-m-d H:i:s" ), $logType) . $message;

	return scholar_scrapper_write_in_file( LOG_FILE, $message );
}


/**
 * @param $object mixed Simple object or array. Ex: {"key": "value"}
 * @param $class string Class name
 *
 * @return mixed Object of class $class
 * @throws ReflectionException
 */
function scholar_scraper_cast_object_to_class( mixed $object, string $class ) {

	if ( ! class_exists( $class ) ) {
		return "Class $class doesn't exist";
	}

	// Check if $object is an array or an object
	if ( ! is_array( $object ) && ! is_object( $object ) ) {
		return "Object is not an array or an object";
	}

	// On créé un objet de la class $class
	$castedObject = new $class();
	// On récupère les informations de la classe $class
	$reflection = new ReflectionClass( $class );

	// On parcours les attributs de l'objet
	foreach ( $object as $key => $value ) {

		// Entrée : l'attribut $key n'existe pas dans la class $class
		//       => Erreur
		if ( ! property_exists( $class, $key ) ) {
			continue;
		}

		// On récupère la classe de l'attribut $key de $class
		$property = $reflection->getProperty( $key );
		$property->setAccessible( true );
		$propertyClass = $property->getType()->getName();


		// Entrée : l'attribut $key de $class est un objet qui étend GenericCollection
		//       => On caste la valeur en tableau d'objets de la classe $propertyClass::$itemClass
		if ( ! empty( $propertyClass ) && is_subclass_of( $propertyClass, GenericCollection::class ) ) {

			// Entrée : La valeur est un objet et non un tableau
			//       => On met l'objet dans un tableau
			if ( ! is_array( $value ) || ! is_numeric( array_key_first( $value ) ) ) {
				$value = [ $value ];
			}

			// On caste chaque objet du tableau en objet de la classe $propertyClass::$itemClass
			$arrayObjects = [];

			// On caste chaque objet du tableau en objet de la classe $propertyClass::$itemClass
			foreach ( $value as $item ) {
				$arrayObjects[] = scholar_scraper_cast_object_to_class( $item, $propertyClass::$itemClass );
			}

			// On ajoute le tableau d'objets à l'objet $castedObject
			$castedObject->$key = new $propertyClass( ...$arrayObjects );

			continue;

		}

		$castedObject->$key = $value;

	}

	return $castedObject;
}
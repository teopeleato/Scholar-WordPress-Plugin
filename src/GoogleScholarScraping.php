<?php

/**
 * Fonction permettant d'installer les dépendances du script python.
 * @return bool True si l'installation s'est bien déroulée, false sinon.
 */
function scholar_scraper_install_requirements() {

	// On vérifie que le fichier requirements.txt existe
	if ( ! file_exists( PYTHON_REQUIREMENTS_PATH ) ) {
		return false;
	}

	// On vérifie que le chemin vers pip est correct
	$pipPath = scholar_scraper_get_setting_value( 'PIP_PATH' );
	if ( ! $pipPath || ! is_executable( $pipPath ) ) {
		return false;
	}

	// On lance la commande d'installation des dépendances
	list( $res, $ret_val ) = scholar_scraper_run_command_try_all_methods(
		sprintf( "%s install -r %s",
			scholar_scraper_get_setting_value( 'PIP_PATH' ),
			PYTHON_REQUIREMENTS_PATH
		)
	);

	// La valeur retournée est 0 : l'installation s'est bien déroulée
	return $ret_val == 0;
}

/**
 * Fonction permettant d'exécuter le script python qui récupère les données de Google Scholar.
 *
 * @return mixed|string
 */
function scholar_scraper_start_scraping() {
	// On vérifie que le script python existe
	if ( ! file_exists( PYTHON_SCRIPT_PATH ) ) {
		return "";
	}

	// On vérifie que le chemin vers python est correct
	$pythonPath = scholar_scraper_get_setting_value( 'PYTHON_PATH' );
	if ( ! $pythonPath || ! is_executable( $pythonPath ) ) {
		return "";
	}

	// On vérifié qu'on a bien accès à la base de données WordPress
	global $wpdb;
	if ( ! isset( $wpdb ) ) {
		return "";
	}

	// On s'assure que les dépendances Python sont bien installées
	scholar_scraper_install_requirements();

	# TODO: Get the scholar users id from the database
	$scholarUsers = array( "1iQtvdsAAAAJ", "dAKCYJgAAAAJ" );

	// On vérifie qu'on a bien récupéré des utilisateurs
	if ( ! count( $scholarUsers ) ) {
		return "";
	}

	# Creating a string with all the scholar users id separated by a space
	foreach ( $scholarUsers as $scholarUser ) {
		$scraperArguments .= $scholarUser . " ";
	}

	// On formate la commande à exécuter
	$command = sprintf(
		"%s %s %s 2>&1",
		scholar_scraper_get_setting_value( 'PYTHON_PATH' ),
		PYTHON_SCRIPT_PATH,
		$scraperArguments
	);

	// On exécute la commande
	list( $res, $ret_var ) = scholar_scraper_run_command_try_all_methods( $command );

	// On vérifie que la commande s'est bien déroulée, sinon on sort de la fonction
	if ( $ret_var != 0 ) {
		return "";
	}

	// On écrit le résultat dans un fichier
	$resFile = fopen( PLUGIN_PATH . "result.txt", "w" );
	fwrite( $resFile, $res );
	fclose( $resFile );

	//TODO: Parse the result to get the JSON and insert it into the database

	// Parse the result to get the JSON
	//$res = json_decode($res, true);
	//var_dump($res);

	return $res;
}

add_action( CRON_HOOK_NAME, 'scholar_scraper_start_scraping', 10, 0 );

<?php

add_filter( 'cron_schedules', 'scholar_scraper_add_custom_cron_intervals' );

/**
 * Ajoute une liste de fréquences personnalisées à la liste des fréquences de WordPress.
 *
 * @param $schedules array La liste des fréquences de WordPress.
 *
 * @return array La liste des fréquences de WordPress avec les fréquences personnalisées.
 * @see https://developer.wordpress.org/reference/hooks/cron_schedules/
 * @see https://developer.wordpress.org/reference/functions/wp_get_schedules/
 * @since 1.0.0
 */
function scholar_scraper_add_custom_cron_intervals( array $schedules = [] ): array {
	if ( empty( CUSTOM_CRON_FREQUENCIES ) ) {
		return $schedules;
	}

	// On récupère les clés du premier élément du tableau $schedules pour
	// vérifier que les intervals personnalisés suivent le même schéma.
	if ( empty( $schedules ) ) {
		$neededKeys = [ 'display', 'interval' ];
	} else {
		// Get first element of the $schedules array to get the keys
		$neededKeys = array_keys( $schedules[ array_key_first( $schedules ) ] );
	}
	sort( $neededKeys );

	// On parcours les fréquences personnalisées définies dans le fichier de configuration
	foreach ( CUSTOM_CRON_FREQUENCIES as $frequency => $data ) {
		// Si mal définie, on passe à la suivante
		if ( ! is_array( $data ) ) {
			continue;
		}

		$currentKeys = array_keys( $data );
		sort( $currentKeys );

		// On vérifie que les données sont correctes à savoir que c'est un tableau, qu'il n'est pas vide et
		// qu'il contient 2 éléments dont les clés sont "interval" et "display"
		if ( empty( $data ) || $neededKeys !== $currentKeys ) {
			continue;
		}

		$schedules[ $frequency ] = $data;
	}

	// On trie le tableau par intervalle croissant
	uasort( $schedules, function ( $a, $b ) {
		return $a['interval'] <=> $b['interval'];
	} );

	return $schedules;
}


/**
 * Créé un événement cron.
 *
 * @param string $startingTime Timestamp de début de l'événement cron. Si vide, la valeur sera calculée en fonction de l'heure actuelle et da la configuration du plugin.
 * @param string $frequency Fréquence de l'événement cron. Si vide, la valeur enregistrée sera utilisée.
 * @param string $hook Nom de l'action à exécuter. Si vide, la valeur par défaut sera utilisée.
 * @param array $args Arguments à passer à l'action. Si vide, aucun argument ne sera passé.
 *
 * @return mixed Le résultat de la fonction wp_schedule_event().
 * @see https://developer.wordpress.org/reference/functions/wp_schedule_event/
 * @since 1.0.0
 */
function scholar_scraper_schedule_event( string $startingTime = "", string $frequency = "", string $hook = CRON_HOOK_NAME, array $args = [] ): mixed {

	// Si pas de timestamp de début, on calcule le timestamp de début en fonction de l'heure actuelle et de la fréquence
	if ( empty( $startingTime ) || ! is_numeric( $startingTime ) ) {
		$startingTime = scholar_scraper_set_specific_time_timestamp();
	}

	// Si pas de fréquence, on récupère la valeur enregistrée
	if ( empty( $frequency ) ) {
		$frequency = scholar_scraper_get_setting_value( 'CRON_FREQUENCY' );
	}

	// Check if the event is scheduled
	if ( wp_get_scheduled_event( CRON_HOOK_NAME ) ) {
		return false;
	}

	return wp_schedule_event( $startingTime, $frequency, $hook, $args );
}


/**
 * Annule l'événement cron.
 *
 * @return mixed Le résultat de la fonction wp_clear_scheduled_hook().
 * @see https://developer.wordpress.org/reference/functions/wp_clear_scheduled_hook/
 * @since 1.0.0
 */
function scholar_scraper_unschedule_event(): mixed {
	// Check if the event is scheduled
	if ( ! wp_get_scheduled_event( CRON_HOOK_NAME ) ) {
		return false;
	}

	return wp_clear_scheduled_hook( CRON_HOOK_NAME );
}


/**
 * Met à jour la fréquence de l'événement cron.
 *
 * @param string|null $new_frequency La nouvelle fréquence de l'événement cron : une clé de wp_get_schedules(). Si null, la valeur définie dans la configuration du plugin sera utilisée.
 * @param string|null $startingTime Timestamp de début de l'événement cron. Si null, la valeur sera calculée en fonction de l'heure actuelle et da la configuration du plugin.
 *
 * @return mixed Le résultat de la fonction scholar_scraper_schedule_event().
 * @see scholar_scraper_schedule_event()
 * @since 1.0.0
 */
function scholar_scraper_update_schedule_event( string $new_frequency = null, string $startingTime = null ): mixed {

	// Get the default value if not set
	if ( empty( $new_frequency ) ) {
		$new_frequency = scholar_scraper_get_setting_value( 'CRON_FREQUENCY' );
	}

	$new_interval = scholar_scraper_get_schedule_interval( $new_frequency );

	// Si pas de timestamp de début, on calcule le timestamp de début en fonction de l'heure actuelle et de la fréquence
	if ( empty( $startingTime ) || ! is_numeric( $startingTime ) ) {
		$startingTime = scholar_scraper_get_next_specific_timestamp(
			"",
			"",
			$new_interval
		);
	}

	// Annulation de l'événement cron existant
	scholar_scraper_unschedule_event();

	// Planification de l'événement cron avec la nouvelle fréquence
	return scholar_scraper_schedule_event( $startingTime, $new_frequency );
}


/**
 * Fonction qui retourne l'intervalle de temps défini pour une fréquence donnée (wp_get_schedules).
 *
 * @param string $frequencyName
 *
 * @return int L'intervalle de temps en secondes. 0 si la fréquence n'existe pas.
 * @since 1.0.0
 */
function scholar_scraper_get_schedule_interval( string $frequencyName = "" ): int {
	// Si pas de fréquence, on récupère la valeur enregistrée
	if ( empty( $frequencyName ) ) {
		$frequencyName = scholar_scraper_get_setting_value( 'CRON_FREQUENCY' );
	}
	$schedules = wp_get_schedules();
	if ( ! array_key_exists( $frequencyName, $schedules ) ) {
		return 0;
	}

	// On récupère la fréquence
	return $schedules[ $frequencyName ]['interval'];
}
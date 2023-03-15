<?php

use Model\ScholarAuthor;
use Model\ScholarAuthorCollection;
use Model\ScholarPublication;


// Cron related actions
add_action( CRON_HOOK_NAME, 'scholar_scraper_start_scraping', PLUGIN_PRIORITY, 0 );
add_action( CRON_HOOK_IMMEDIATE_NAME, 'scholar_scraper_start_scraping', PLUGIN_PRIORITY, 0 );


/**
 * Fonction permettant d'installer les dépendances du script python.
 * @return bool True si l'installation s'est bien déroulée, false sinon.
 * @since 1.0.0
 */
function scholar_scraper_install_requirements(): bool {

    // On vérifie que le fichier requirements.txt existe
    if ( ! is_file( PYTHON_REQUIREMENTS_PATH ) || ! is_readable( PYTHON_REQUIREMENTS_PATH ) ) {
        return false;
    }

    // On vérifie que le chemin vers pip est correct
    $pipPath = scholar_scraper_get_setting_value( 'PIP_PATH' );
    if ( ! $pipPath || ! is_executable( $pipPath ) ) {
        return false;
    }

    // On lance la commande d'installation des dépendances
    list( $res, $ret_val ) = scholar_scraper_run_command_try_all_methods(
        sprintf( "%s install --upgrade -r %s",
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
 * @return mixed|string Le résultat de l'exécution du script python.
 * @throws ReflectionException Si un problème survient lors de la création d'un objet.
 * @since 1.0.0
 */
function scholar_scraper_start_scraping() {

    // Check if the cron is already executing
    if ( get_transient( CRON_TRANSIENT ) ) {
        scholar_scraper_log( LOG_TYPE::INFO, "Cron already executing." );

        return false;
    }

    set_transient( CRON_TRANSIENT, true, CRON_TRANSIENT_RESET_AFTER );

    // On vérifie que le script python existe
    if ( ! is_file( PYTHON_SCRIPT_PATH ) || ! is_readable( PYTHON_SCRIPT_PATH ) ) {
        scholar_scraper_on_cron_exec_error( "Python script not found" );

        return "";
    }

    // On vérifie que le chemin vers python est correct
    $pythonPath = scholar_scraper_get_setting_value( 'PYTHON_PATH' );
    if ( ! $pythonPath || ! is_executable( $pythonPath ) ) {
        scholar_scraper_on_cron_exec_error( "Python path not found" );

        return "";
    }

    // On s'assure que les dépendances Python sont bien installées
    if ( ! scholar_scraper_install_requirements() ) {
        scholar_scraper_on_cron_exec_error( "Python requirements not installed" );

        return "";
    }

    // Get all the users that are defined as researchers
    $scholarUsers = [];

    foreach ( scholar_scraper_get_setting_value( 'RESEARCHERS_ROLES' ) as $role ) {
        $scholarUsers = array_merge(
            $scholarUsers,
            scholar_scraper_get_list_meta_key( $role, scholar_scraper_get_setting_value( 'META_KEY_SCHOLAR_ID' ) )
        );
    }

    // On vérifie qu'on a bien récupéré des utilisateurs
    if ( ! count( $scholarUsers ) ) {
        scholar_scraper_on_cron_exec_error( "No scholar users found" );

        return "";
    }

    $scraperArguments = "";

    # Creating a string with all the scholar users id separated by a space
    foreach ( $scholarUsers as $scholarUser ) {
        $scraperArguments .= $scholarUser . " ";
    }

    // On formate la commande à exécuter
    $command = sprintf(
        "%s %s maxThreads=%s  %s 2>&1",
        scholar_scraper_get_setting_value( 'PYTHON_PATH' ),
        PYTHON_SCRIPT_PATH,
        scholar_scraper_get_setting_value( 'PYTHON_API_THREADS' ),
        trim( $scraperArguments )
    );

    // On exécute la commande
    list( $res, $ret_var ) = scholar_scraper_run_command_try_all_methods( $command );

    // On vérifie que la commande s'est bien déroulée, sinon on sort de la fonction
    if ( $ret_var != 0 ) {
        scholar_scraper_on_cron_exec_error();

        return "";
    }

    // On écrit le résultat dans un fichier
    scholar_scraper_write_in_file( RESULTS_FILE, $res, false );

    // On décode le résultat en objets PHP
    $decodedRes = scholar_scraper_decode_results( $res );

    // On serialise le résultat
    $serialized = serialize( $decodedRes );

    // On écrit le résultat sérialisé dans un fichier
    scholar_scraper_write_in_file( SERIALIZED_RESULTS_FILE, $serialized, false );


    delete_transient( CRON_TRANSIENT );

    return $res;
}


/**
 * Fonction permettant de gérer les actions à effectuer lorsqu'une erreur survient lors de l'exécution du cron.
 *
 * @param string|null $message Le message d'erreur.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_on_cron_exec_error( string $message = null ) {
    delete_transient( CRON_TRANSIENT );

    if ( ! empty( $message ) ) {
        scholar_scraper_log( LOG_TYPE::ERROR, $message );
    }

    $retryAfter = scholar_scraper_get_setting_value( 'CRON_RETRY_AFTER' );
    scholar_scraper_log( LOG_TYPE::INFO, "Retrying in " . $retryAfter . " minutes" );

    // On réessaie dans CRON_RETRY_AFTER secondes
    wp_schedule_single_event( time() + ( $retryAfter * MINUTE_IN_SECONDS ), CRON_HOOK_IMMEDIATE_NAME );

}


/**
 * Fonction permettant d'afficher le résultat de l'exécution du script python.
 *
 * @param mixed $atts Les attributs du shortcode.
 *
 * @return string Le résultat de l'exécution du script python.
 * @throws ReflectionException Si une erreur survient lors de la récupération des objets.
 * @since 1.0.0
 */
function scholar_scraper_display_result( $atts ): string {

    // Get the attributes passed to the shortcode
    $atts = shortcode_atts(
        array(
            'number_papers_to_show' => DEFAULT_NUMBER_OF_PAPERS_TO_SHOW,
            'sort_by_field'         => DEFAULT_PAPERS_SORT_FIELD,
            'sort_by_direction'     => DEFAULT_PAPERS_SORT_DIRECTION,
            'display_type'          => DEFAULT_PAPERS_DISPLAY_TYPE,
            'allow_search'          => DEFAULT_PAPERS_ALLOW_SEARCH,
            'search_query'          => null,
            'block_id'              => uniqid( 'scholar_scraper_block_' ),
            'number_lines_abstract' => DEFAULT_NUMBER_LINES_ABSTRACT,
        ),
        $atts,
        'scholar_scraper'
    );

    // Unescape all the strings in the attributes
    $atts = stripslashes_deep( $atts );

    $toReturn = "";

    // On vérifie que l'attribut number_papers_to_show est bien un nombre
    if ( ! is_numeric( trim( $atts['number_papers_to_show'] ) ) ) {
        $atts['number_papers_to_show'] = DEFAULT_NUMBER_OF_PAPERS_TO_SHOW;
    }

    // On récupère le nombre de publications à afficher
    $nbPapersToShow = ( (int) $atts['number_papers_to_show'] ) - 1;


    // On vérifie que l'attribut sort_by_field est bien un champ de la classe ScholarPublication
    $posibleSortFields = ScholarPublication::get_non_array_fields();
    if ( ! array_key_exists( trim( $atts['sort_by_field'] ), $posibleSortFields ) ) {
        $atts['sort_by_field'] = DEFAULT_PAPERS_SORT_FIELD;
    }

    $sortField = $atts['sort_by_field'];


    // On vérifie que l'attribut sort_by_direction est bien une direction de tri possible
    $posibleSortDirections = array( 'asc', 'desc' );
    if ( ! in_array( strtolower( trim( $atts['sort_by_direction'] ) ), $posibleSortDirections ) ) {
        $atts['sort_by_direction'] = DEFAULT_PAPERS_SORT_DIRECTION;
    }

    $sortDirection = $atts['sort_by_direction'];


    $possibleDisplayTypes = PAPERS_DISPLAY_TYPES;
    if ( ! array_key_exists( trim( $atts['display_type'] ), $possibleDisplayTypes ) ) {
        $atts['display_type'] = DEFAULT_PAPERS_DISPLAY_TYPE;
    }
    $displayType      = $atts['display_type'];
    $templateFilePath = PLUGIN_DIR . 'src/Template/' . PAPERS_DISPLAY_TYPES[ $displayType ]['template-file'];
    $containerClass   = 'scholar-scraper-publications ' . PAPERS_DISPLAY_TYPES[ $displayType ]['container-class'];

    $searchQuery = null;

    // Force cast to boolean
    $atts['allow_search'] = (bool) $atts['allow_search'];
    if ( $atts['allow_search'] ) {

        $searchQuery = $atts['search_query'];

        $data = [
            'block_id' => $atts['block_id'] ?? uniqid( 'scholar_scraper_block_' ),
        ];


        if ( ! wp_doing_ajax() ) {
            ob_start();
            include PLUGIN_DIR . 'src/Template/SearchForm.php';
            $toReturn .= ob_get_clean();
        }
    }

    if ( ! is_array( $atts['number_lines_abstract'] ) ) {
        // Transforme la chaine de caractère en tableau
        $atts['number_lines_abstract'] = json_decode( $atts['number_lines_abstract'], true );
    }

    // On vérifie que l'attribut number_lines_abstract est bien un nombre
    if ( ! isset( $atts['number_lines_abstract'][ $displayType ] ) || ! is_numeric( trim( $atts['number_lines_abstract'][ $displayType ] ) ) || $atts['number_lines_abstract'][ $displayType ] < 0 ) {
        $atts['number_lines_abstract'][ $displayType ] = DEFAULT_NUMBER_LINES_ABSTRACT[ $displayType ];
    }

    $numberLinesAbstract = (int) $atts['number_lines_abstract'][ $displayType ];

    // On s'assure que le fichier contenant le template existe et est lisible
    if ( ! is_file( $templateFilePath ) || ! is_readable( $templateFilePath ) ) {

        return "<div class='scholar-scraper-publications error'>
			<p>Unfortunately, our researchers are currently on vacation...<br/>Please try again later.</p>
		</div>";
    }

    $scholarPublicationsCollection = scholar_scraper_get_publications( $searchQuery );

    if ( is_null( $scholarPublicationsCollection ) || ( $totalPapers = $scholarPublicationsCollection->count() ) === 0 ) {

        if ( ! is_null( $searchQuery ) ) {
            return "<div class='scholar-scraper-publications error'>
				<p>No publication found for the query <code>$searchQuery</code>.<br/>Please try again later.</p>
			</div>";
        }

        return "<div class='scholar-scraper-publications error'>
			<p>Unfortunately, our researchers are currently on vacation...<br/>Please try again later.</p>
		</div>";
    }

    // Order the publications by $atts['sort_by_field']. If the field is the same, the alphabetical order is used on title.
    // If the $atts['sort_by_field'] is not set, the publication is put at the end of the list.
    $scholarPublicationsCollection->usort( function ( $a, $b ) use ( $sortField, $sortDirection ): int {
        if ( ! isset( $a ) || ! isset( $b ) ) {
            return 0;
        }

        // Si les deux publications n'ont pas de valeur pour le champ de tri, on trie par ordre alphabétique sur le titre
        if ( ! isset( $a->$sortField ) && ! isset( $b->$sortField ) ) {
            // Tri alphabétique sur le titre en fonction de la direction de tri
            if ( $sortDirection === 'desc' ) {
                return strcmp( $b->title, $a->title );
            }

            return strcmp( $a->title, $b->title );
        }


        // Si la première publication n'a pas de valeur pour le champ de tri, on la met à la fin de la liste
        if ( ! isset( $a->$sortField ) ) {
            return 1;
        }

        // Si la deuxième publication n'a pas de valeur pour le champ de tri, on la met à la fin de la liste
        if ( ! isset( $b->$sortField ) ) {
            return - 1;
        }

        // Si les deux publications ont la même valeur pour le champ de tri, on trie par ordre alphabétique sur le titre
        if ( $a->$sortField === $b->$sortField ) {
            // Tri alphabétique sur le titre en fonction de la direction de tri
            if ( $sortDirection === 'desc' ) {
                return strcmp( $b->title, $a->title );
            }

            return strcmp( $a->title, $b->title );
        }


        // Tri en fonction de la direction de tri
        if ( $sortDirection === 'desc' ) {
            return $b->$sortField <=> $a->$sortField;
        }

        return $a->$sortField <=> $b->$sortField;
    } );

    // On affiche les publications
    $toReturn .= "<div class='$containerClass'>";

    for ( $i = 0; $i <= $nbPapersToShow && $i < $totalPapers; $i ++ ) {

        $publication = $scholarPublicationsCollection->get( $i );
        if ( ! isset( $publication ) || ! isset( $publication->title ) ) {
            continue;
        }

        ob_start();
        include( $templateFilePath );
        $toReturn .= ob_get_clean();

    }

    $toReturn .= "</div>";

    return $toReturn;
}


/**
 * Fonction permettant de récupérer le résultat de l'exécution du script python.
 *
 * @return ScholarAuthorCollection Le résultat de l'exécution du script python : une collection d'auteurs.
 *
 * @throws ReflectionException Si une erreur survient lors de la création d'un objet.
 * @since 1.0.0
 */
function scholar_scraper_decode_results( string $results ): ScholarAuthorCollection {
    $results = json_decode( $results, true );

    $scholarUsers = new ScholarAuthorCollection();

    foreach ( $results as $user ) {
        $scholarUser = scholar_scraper_cast_object_to_class( $user, ScholarAuthor::class );

        if ( ! isset( $scholarUser ) ) {
            continue;
        }

        if ( $scholarUser->publications->isEmpty() ) {
            continue;
        }


        $scholarUsers->add( $scholarUser );
    }

    return $scholarUsers;
}

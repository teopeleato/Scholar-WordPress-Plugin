<?php

use Model\GenericCollection;
use Model\ScholarAuthorCollection;
use Model\ScholarPublication;
use Model\ScholarPublicationCollection;


/**
 * Class des types de logs.
 * @since 1.0.0
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
 * Fonction pour récupérer les paramètres du plugin ou les valeurs par défaut si ils ne sont pas encore définis.
 * @return array|false|null Les paramètres du plugin ou les valeurs par défaut si ils ne sont pas encore définis.
 * @since 1.0.0
 */
function scholar_scraper_get_settings_or_default() {

    $default = [];

    foreach ( PLUGIN_SETTINGS as $setting_acronym => $setting ) {
        $default[ $setting['name'] ] = scholar_scraper_get_default_value( $setting_acronym );
    }

    $settings = get_option( OPTION_GROUP );

    if ( is_null( $settings ) || $settings === false ) {
        return $default;
    }

    // Remove all keys that are not in the default settings
    $settings = array_intersect_key( $settings, $default );

    // Sanitize settings
    $settings = scholar_scraper_sanitize_settings( $settings, false );
    // Add all keys that are not in the settings
    $settings = array_merge( $default, $settings );

    return $settings;
}


/**
 * Initialise les paramètres du plugin s'ils ne sont pas encore définis.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_set_default_settings(): void {
    $settings = scholar_scraper_get_settings_or_default();
    update_option( OPTION_GROUP, $settings );
}


/**
 * Récupère la valeur d'un paramètre.
 *
 * @param string $setting_acronym Acronyme du paramètre.
 *
 * @return mixed Valeur du paramètre ou valeur par défaut si le paramètre n'est pas défini.
 * @since 1.0.0
 */
function scholar_scraper_get_setting_value( string $setting_acronym ) {
    $settings = get_option( OPTION_GROUP );

    if ( ! isset( PLUGIN_SETTINGS[ $setting_acronym ] ) ) {
        return null;
    }

    $requestedSetting = PLUGIN_SETTINGS[ $setting_acronym ];

    if ( empty( $settings[ $requestedSetting['name'] ] ) ) {
        return scholar_scraper_get_default_value( $setting_acronym );
    }

    // If the type is input, check that the value matches the regex
    if ( $requestedSetting['type'] === 'input' ) {

        if ( ! isset( $requestedSetting['regex'] ) ) {
            return $settings[ $requestedSetting['name'] ];
        }

        $regex = $requestedSetting['regex'];

        if ( ! preg_match( $regex, $settings[ $requestedSetting['name'] ] ) ) {
            return scholar_scraper_get_default_value( $setting_acronym );
        }

    } elseif ( $requestedSetting['type'] === 'number' ) {

        if ( ! is_numeric( $settings[ $requestedSetting['name'] ] ) ) {
            return scholar_scraper_get_default_value( $setting_acronym );
        }

        if ( isset( $requestedSetting['min'] ) && $settings[ $requestedSetting['name'] ] < $requestedSetting['min'] ) {
            return scholar_scraper_get_default_value( $setting_acronym );
        }

        if ( isset( $requestedSetting['max'] ) && $settings[ $requestedSetting['name'] ] > $requestedSetting['max'] ) {
            return scholar_scraper_get_default_value( $setting_acronym );
        }

    } elseif ( $requestedSetting['type'] === 'select' ) {
        $allowed_values = array_keys( $requestedSetting['options'] );

        if ( ! in_array( $settings[ $requestedSetting['name'] ], $allowed_values ) ) {
            return scholar_scraper_get_default_value( $setting_acronym );
        }

    } elseif ( $requestedSetting['type'] === 'multi_select' ) {
        $allowed_values  = array_keys( $requestedSetting['options'] );
        $selected_values = $settings[ $requestedSetting['name'] ];

        if ( ! is_array( $selected_values ) ) {
            return scholar_scraper_get_default_value( $setting_acronym );
        }

        foreach ( $selected_values as $selected_value ) {
            if ( ! in_array( $selected_value, $allowed_values ) ) {
                return scholar_scraper_get_default_value( $setting_acronym );
            }
        }
    }

    return $settings[ $requestedSetting['name'] ];
}


/**
 * Récupère le nom d'un paramètre en BDD.
 *
 * @param string $setting_acronym Acronyme du paramètre.
 *
 * @return string|null Nom du paramètre en BDD. Null si l'acronyme n'existe pas dans le tableau PLUGIN_SETTINGS.
 * @since 1.0.0
 */
function scholar_scraper_get_setting_name( string $setting_acronym ): ?string {
    if ( ! isset( PLUGIN_SETTINGS[ $setting_acronym ] ) ) {
        return null;
    }

    return sprintf( "%s[%s]", OPTION_GROUP, PLUGIN_SETTINGS[ $setting_acronym ]['name'] );
}


/**
 * Récupère les noms de tous les paramètres en BDD.
 *
 * @return array Tableau des noms de tous les paramètres en BDD.
 * @since 1.0.0
 */
function scholar_scraper_get_settings_names(): array {
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
 * @since 1.0.0
 */
function scholar_scraper_is_plugin_setting( string $setting_name ): bool {
    return in_array( $setting_name, scholar_scraper_get_settings_names() );
}


/**
 * Modifie l'heure d'un timestamp.
 *
 * @param string $timestamp Timestamp de la date à modifier
 * @param string $wanted_time Heure au format H:i:s
 *
 * @return string Timestamp modifié au format Y-m-d H:i:s
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @return bool True si l'écriture s'est bien déroulée, false sinon.
 * @since 1.0.0
 */
function scholar_scraper_write_in_file( string $filePath, string $content, bool $append = true, bool $add_new_line = true ): bool {
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
 * @since 1.0.0
 */
function scholar_scraper_log( string $logType, string $message ): bool {

    // Entrée : Le message est vide
    //       => On ne crée pas de log
    if ( empty( $message ) ) {
        return false;
    }

    // Entrée : Le type de message n'est pas valide
    //       => On ne crée pas de log
    if ( ! in_array( $logType, LOG_TYPE::ALL ) ) {
        return false;
    }

    // On récupère la longueur maximale des types de messages
    $maxLength = max( array_map( 'strlen', LOG_TYPE::ALL ) ) + 3;
    // On ajoute le timestamp et le type de message au message
    $message = sprintf( "%s\t%-{$maxLength}s ", date( "Y-m-d H:i:s" ), $logType ) . $message;

    return scholar_scraper_write_in_file( LOG_FILE, $message );
}


/**
 * @param $object mixed Simple object or array. Ex: {"key": "value"}
 * @param $class string Class name
 *
 * @return mixed Object of class $class
 * @throws ReflectionException If the class doesn't exist.
 * @since 1.0.0
 */
function scholar_scraper_cast_object_to_class( $object, string $class ) {

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


        if ( ! empty( $propertyClass ) ){

            // Entrée : l'attribut $key de $class est un objet qui étend GenericCollection
            //       => On caste la valeur en tableau d'objets de la classe $propertyClass::$itemClass
            if( is_subclass_of( $propertyClass, GenericCollection::class ) ) {

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
            // Entrée : l'attribut $key de $class est un objet qui étend GenericObject
            else if( in_array( $propertyClass, ["bool", "boolean", "int", "integer", "float", "double", "string", "array"] ) ) {
                settype( $value, $propertyClass );
            }

        }


        $castedObject->$key = $value;

    }

    return $castedObject;
}


/**
 * Fonction permettant de récupérer les utilisateurs ayant un rôle donné.
 *
 * @param string $role Le rôle des utilisateurs à récupérer.
 *
 * @return array Un tableau contenant les utilisateurs ayant le rôle donné.
 * @since 1.0.0
 */
function scholar_scraper_get_users_having_role( string $role ): array {
    $users = get_users( [ 'role' => $role ] );

    return $users;
}


/**
 * Fonction permettant de récupérer les utilisateurs ayant un rôle donné ainsi que leurs métadonnées.
 *
 * @param string $role Le rôle des utilisateurs à récupérer.
 *
 * @return array Un tableau contenant les utilisateurs ayant le rôle donné ainsi que leurs métadonnées.
 * @since 1.0.0
 */
function scholar_scraper_get_users_with_meta_having_role( string $role ): array {
    $users = scholar_scraper_get_users_having_role( $role );

    $usersWithMeta = [];

    foreach ( $users as $user ) {

        $user->meta      = get_user_meta( $user->ID );
        $usersWithMeta[] = $user;
    }

    return $usersWithMeta;
}


/**
 * Fonction qui retourne la liste valeurs des métadonnées d'un rôle donné.
 *
 * @param string $role Le rôle des utilisateurs à récupérer.
 * @param string $metaKey La clé de la métadonnée à récupérer.
 *
 * @return array Un tableau contenant les valeurs des métadonnées d'un rôle donné.
 * @since 1.0.0
 */
function scholar_scraper_get_list_meta_key( string $role, string $metaKey ): array {
    $users = scholar_scraper_get_users_with_meta_having_role( $role );

    $list = [];

    foreach ( $users as $user ) {
        if ( isset( $user->meta[ $metaKey ] ) && ! empty( $user->meta[ $metaKey ][0] ) ) {
            $list[] = $user->meta[ $metaKey ][0];
        }
    }

    return $list;
}


/**
 * Fonction permettant de récupérer les publications qui ont été récupérées par le scraper.
 * Si le paramètre $searchQuery est passé, la fonction retourne les publications correspondant à la recherche.
 * La recherche se fait sur :
 * * le titre des publications
 * * la description des publications
 * * l'année de publication des publications
 * * le nom de l'auteur des publications
 * * les intérêts de l'auteur des publications
 *
 * @param string|null $searchQuery Le paramètre à rechercher dans les publications.
 *
 * @return ScholarPublicationCollection|null Un tableau contenant les publications. Null si aucun fichier contenant les publications n'a été trouvé.
 * @throws ReflectionException Si un problème survient lors de la création d'un objet.
 * @since 1.1.0
 */
function scholar_scraper_get_publications( string $searchQuery = null ): ?ScholarPublicationCollection {
    // Entrée : le fichier contenant les résultats sérialisés n'existe pas ou n'est pas lisible
    //       => On essaie de voir si le fichier contenant les résultats non sérialisés existe et est lisible
    if ( ! is_file( SERIALIZED_RESULTS_FILE ) || ! is_readable( SERIALIZED_RESULTS_FILE ) ) {

        // Entrée : le fichier contenant les résultats non sérialisés n'existe pas ou n'est pas lisible
        //       => On affiche un message d'erreur
        if ( ! is_file( RESULTS_FILE ) || ! is_readable( RESULTS_FILE ) ) {
            return null;
        }

        $res = file_get_contents( RESULTS_FILE );

        // On décode le résultat en objets PHP
        $decodedRes = scholar_scraper_decode_results( $res );

        // On serialise le résultat
        $serialized = serialize( $decodedRes );

        // On écrit le résultat sérialisé dans un fichier
        scholar_scraper_write_in_file( SERIALIZED_RESULTS_FILE, $serialized, false );

    }

    // Get the content of the result file
    $res                           = file_get_contents( SERIALIZED_RESULTS_FILE );
    $res                           = unserialize( $res );
    $scholarPublicationsCollection = new ScholarPublicationCollection();

    // Ensure that the result is a ScholarAuthorCollection object
    if ( ! ( $res instanceof ScholarAuthorCollection ) ) {
        return null;
    }

    // Add all the publications of all the users to the collection
    foreach ( $res as $scholarUser ) {

        $publications = $scholarUser->publications->values();

        // Filter the publications if a search is passed
        // The search should be done like it would be with a search engine
        if ( ! empty( $searchQuery ) ) {
            $publications = array_filter( $publications,
                function ( ScholarPublication $scholarPublication ) use ( $searchQuery, $scholarUser ) {
                    $publicationTitle   = $scholarPublication->title ?? '';
                    $publicationDesc    = $scholarPublication->abstract ?? '';
                    $publicationAuthors = $scholarPublication->author ?? '';
                    $publicationAuthors .= isset( $scholarUser->name ) ? ' ' . $scholarUser->name : '';
                    $publicationYear    = $scholarPublication->pub_year ?? '';
                    $authorInterests    = $scholarUser->interests ?? [];

                    // We will test all the search terms in the publication title, description, authors and author interests
                    $searchTerms = [ $searchQuery, ...explode( ' ', $searchQuery ) ];

                    // Search case insensitive, not depending accents and special characters
                    $publicationTitle   = remove_accents( strtolower( $publicationTitle ) );
                    $publicationDesc    = remove_accents( strtolower( $publicationDesc ) );
                    $publicationAuthors = remove_accents( strtolower( $publicationAuthors ) );
                    $authorInterests    = array_map( function ( $interest ) {
                        return remove_accents( strtolower( $interest ) );
                    }, $authorInterests );


                    // Searching
                    foreach ( $searchTerms as $searchTerm ) {
                        $searchTerm = remove_accents( strtolower( $searchTerm ) );

                        // If the search term is found in the publication title, description, authors or author interests, we return true
                        if (
                            false !== strpos( $publicationTitle, $searchTerm )
                            || false !== strpos( $publicationDesc, $searchTerm )
                            || false !== strpos( $publicationAuthors, $searchTerm )
                            || false !== strpos( $publicationYear, $searchTerm )
                            || ! empty( array_filter( $authorInterests, function ( $interest ) use ( $searchTerm ) {
                                return false !== strpos( $interest, $searchTerm );
                            } ) )
                        ) {
                            // If the publication author does not contain the user name, we add it at the beginning of the publication name
                            if ( empty( $scholarPublication->author ) ) {
                                $scholarPublication->author = $scholarUser->name;
                            } elseif ( ! str_contains( $scholarPublication->author, $scholarUser->name ) ) {
                                $scholarPublication->author = $scholarUser->name . ' and ' . $scholarPublication->author;
                            }

                            return true;
                        }
                    }

                    return false;
                }
            );
        } else {
            // If the publication author does not contain the user name, we add it at the beginning of the publication name
            foreach ( $publications as $publication ) {
                if ( empty( $publication->author ) ) {
                    $publication->author = $scholarUser->name;
                } elseif ( ! str_contains( $publication->author, $scholarUser->name ) ) {
                    $publication->author = $scholarUser->name . ' and ' . $publication->author;
                }
            }
        }

        $scholarPublicationsCollection->add( ...$publications );
    }

    return $scholarPublicationsCollection;
}
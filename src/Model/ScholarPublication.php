<?php

namespace Model;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class ScholarPublication.
 * Represents a publication of a ScholarAuthor.
 */
class ScholarPublication {

    /**
     * @var string $author List of the author names that contributed to this publication.
     */
    public string $author;

    /**
     * @var int $num_citations Number of citations for this publication.
     */
    public int $num_citations;

    /**
     * @var array $cites_per_year An array of integers, where the index is the year and the value is the number of citations.
     */
    public array $cites_per_year;

    /**
     * @var string $pub_url The publication URL.
     */
    public string $pub_url;

    /**
     * @var string $author_pub_id The id of the paper on Google Scholar from an author page.
     */
    public string $author_pub_id;

    /**
     * @var string $title The title of this publication.
     */
    public string $title;

    /**
     * @var string $abstract The abstract of this publication.
     */
    public string $abstract;

    /**
     * @var string $author_id List of the corresponding author ids of the authors that contributed to the Publication.
     */
    public string $author_id;

    /**
     * @var string $pub_year The year this publication was published.
     */
    public string $pub_year;

    /**
     * @var string $venue The venue this publication was published in.
     */
    public string $venue;

    /**
     * @var string $journal The journal this publication was published in.
     */
    public string $journal;

    /**
     * @var int $volume Number of years a publication has been circulated.
     */
    public int $volume;

    /**
     * @var int $number NA number of a publication.
     */
    public int $number;

    /**
     * @var string $pages Range of pages this publication was published in.
     */
    public string $pages;

    /**
     * @var string $publisher The publisher of this publication.
     */
    public string $publisher;

    /**
     * @var string $citation A formatted string to cite this publication.
     */
    public string $citation;

    /**
     * @var string $url_related_articles A string containing the URL to the related articles.
     */
    public string $url_related_articles;


    /**
     * Retourne un tableau associatif des champs de la classe ScholarPublication.
     * La clé est le nom du champ et la valeur est le nom du champ avec les underscores remplacés par des espaces et la première lettre en majuscule.
     *
     * @return array
     */
    public static function get_non_array_fields(): array {
        $reflection          = new ReflectionClass( self::class );
        $publications_fields = array_filter(
            array_map(
                function ( ReflectionProperty $property ): ?array {
                    if ( $property->isStatic() || $property->getType()->getName() == 'array' ) {
                        return null;
                    }

                    return [ $property->getName() => ucfirst( str_replace( '_', ' ', $property->getName() ) ) ];
                },
                $reflection->getProperties( ReflectionProperty::IS_PUBLIC )
            ),
            function ( $value ) {
                return $value !== null;
            }
        );

        // Make the array associative
        $publications_fields = array_reduce(
            $publications_fields,
            function ( $carry, $item ) {
                return array_merge( $carry, $item );
            },
            []
        );

        return $publications_fields;
    }

}
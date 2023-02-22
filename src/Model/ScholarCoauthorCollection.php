<?php

namespace Model;

/**
 * Class ScholarCoauthorCollection.
 * Represents a collection of ScholarCoauthor objects.
 */
class ScholarCoauthorCollection extends GenericCollection {

    /**
     * @var string The class of the items in the collection.
     */
    public static $itemClass = ScholarCoauthor::class;


    /**
     * Constructor.
     *
     * @param ScholarCoauthor ...$coauthors The coauthors to add to the collection.
     */
    public function __construct( ScholarCoauthor ...$coauthors ) {
        parent::__construct( ...$coauthors );
    }

}
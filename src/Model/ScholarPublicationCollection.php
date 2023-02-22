<?php

namespace Model;

/**
 * Class ScholarPublicationCollection.
 * Represents a collection of ScholarPublication objects.
 */
class ScholarPublicationCollection extends GenericCollection {

    /**
     * @var string The class of the items in the collection.
     */
    public static $itemClass = ScholarPublication::class;


    /**
     * Constructor.
     *
     * @param ScholarPublication ...$publications The publications to add to the collection.
     */
    public function __construct( ScholarPublication ...$publications ) {
        parent::__construct( ...$publications );
    }

}
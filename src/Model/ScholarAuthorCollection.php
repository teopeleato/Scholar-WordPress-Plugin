<?php

namespace Model;

/**
 * Class ScholarAuthorCollection.
 * Represents a collection of ScholarAuthor objects.
 */
class ScholarAuthorCollection extends GenericCollection {

    /**
     * @var string The class of the items in the collection.
     */
    public static $itemClass = ScholarAuthor::class;


    /**
     * Constructor.
     *
     * @param ScholarAuthor ...$authors The authors to add to the collection.
     */
    public function __construct( ScholarAuthor ...$authors ) {
        parent::__construct( ...$authors );
    }

}
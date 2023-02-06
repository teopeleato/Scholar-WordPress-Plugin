<?php

namespace Model;

class ScholarAuthorCollection extends GenericCollection {
	public static $itemClass = ScholarAuthor::class;

	public function __construct( ScholarAuthor ...$authors ) {
		parent::__construct( ...$authors );
	}

}
<?php

namespace Model;

class ScholarPublicationCollection extends GenericCollection {
	public static $itemClass = ScholarPublication::class;

	public function __construct( ScholarPublication ...$publications ) {
		parent::__construct( ...$publications );
	}

}
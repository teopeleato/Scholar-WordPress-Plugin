<?php

namespace Model;

class ScholarCoauthorCollection extends GenericCollection {
	public static $itemClass = ScholarCoauthor::class;

	public function __construct( ScholarCoauthor ...$coauthors ) {
		parent::__construct( ...$coauthors );
	}

}
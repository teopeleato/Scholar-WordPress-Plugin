<?php

namespace Model;

/**
 * Class ScholarCoauthor.
 * Represents a coauthor of a ScholarAuthor.
 */
class ScholarCoauthor {

	/**
	 * @var string $scholar_id The Google Scholar ID of the author.
	 */
	public string $scholar_id;

	/**
	 * @var string $name The name of the author.
	 */
	public string $name;

	/**
	 * @var string $affiliation The affiliation of the coauthor.
	 */
	public string $affiliation;
}
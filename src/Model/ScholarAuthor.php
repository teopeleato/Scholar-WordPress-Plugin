<?php

namespace Model;

/**
 * Class ScholarAuthor.
 * Represents an author on Google Scholar.
 */
class ScholarAuthor extends ScholarCoauthor {

	/**
	 * @var string $organization The Google Scholar ID of the organization the user is affiliated with.
	 */
	public string $organization = "";

	/**
	 * @var ScholarCoauthorCollection $coauthors A collection of ScholarCoauthor objects.
	 */
	public ScholarCoauthorCollection $coauthors;

	/**
	 * @var array $cites_per_year An array of integers, where the index is the year and the value is the number of citations.
	 */
	public array $cites_per_year;

	/**
	 * @var int $citedby An integer representing the number of times the user has been cited.
	 */
	public int $citedby;

	/**
	 * @var array $interests An array of strings, where each string is an interest.
	 */
	public array $interests;

	/**
	 * @var ScholarPublicationCollection $publications A collection of ScholarPublication objects.
	 */
	public ScholarPublicationCollection $publications;

	/**
	 * @var string $homepage The homepage of the user.
	 */
	public string $homepage;


}
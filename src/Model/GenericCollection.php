<?php

namespace Model;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class GenericCollection.
 * Represents a generic collection of objects.
 */
abstract class GenericCollection implements IteratorAggregate {

	/**
	 * @var string The class of the items in the collection.
	 */
	public static $itemClass;

	/**
	 * @var array The items in the collection.
	 */
	protected array $values = [];


	/**
	 * Constructor.
	 *
	 * @param mixed ...$values The items to add to the collection.
	 */
	public function __construct( ...$values ) {
		$this->values = $values;
	}


	/**
	 * Returns an iterator for the collection.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->values );
	}


	/**
	 * Returns the number of items in the collection.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->values );
	}


	/**
	 * Adds items to the collection.
	 *
	 * @param ...$values mixed The items to add to the collection.
	 *
	 * @return void
	 */
	public function add( ...$values ): void {
		foreach ( $values as $value ) {
			if ( ! $this->contains( $value ) ) {
				$this->values[] = $value;
			}
		}
	}


	/**
	 * Checks if the collection contains a given item.
	 *
	 * @param mixed $value The item to check.
	 *
	 * @return bool True if the collection contains the item, false otherwise.
	 */
	public function contains( $value ): bool {
		return in_array( $value, $this->values, true );
	}


	/**
	 * Removes items from the collection.
	 *
	 * @param ...$values mixed The items to remove from the collection.
	 *
	 * @return void
	 */
	public function remove( ...$values ): void {
		foreach ( $values as $value ) {
			$key = array_search( $value, $this->values, true );
			if ( false !== $key ) {
				unset( $this->values[ $key ] );
			}
		}
	}


	/**
	 * Removes all items from the collection.
	 *
	 * @return void
	 */
	public function clear(): void {
		$this->values = [];
	}


	/**
	 * Checks if the collection is empty.
	 *
	 * @return bool True if the collection is empty, false otherwise.
	 */
	public function isEmpty(): bool {
		return empty( $this->values );
	}


	/**
	 * Returns the first item in the collection.
	 *
	 * @return false|mixed The first item in the collection, or false if the collection is empty.
	 */
	public function first() {
		return reset( $this->values );
	}


	/**
	 * Returns the last item in the collection.
	 *
	 * @return false|mixed The last item in the collection, or false if the collection is empty.
	 */
	public function last() {
		return end( $this->values );
	}


	/**
	 * Returns the item at the given key.
	 *
	 * @param int $key The key of the item to get.
	 *
	 * @return mixed|null The item at the given key, or null if the key does not exist.
	 */
	public function get( int $key ) {
		return $this->values[ $key ] ?? null;
	}


	/**
	 * Sets the item at the given key.
	 *
	 * @param int $key The key of the item to set.
	 * @param mixed $value The value to set.
	 *
	 * @return void
	 */
	public function set( int $key, $value ): void {
		$this->values[ $key ] = $value;
	}


	/**
	 * Returns the values of the collection.
	 *
	 * @return array The keys of the collection.
	 */
	public function values(): array {
		return array_values( $this->values );
	}


	/**
	 * Sorts the collection using a user-defined comparison function.
	 *
	 * @param callable $callback The callback to use for sorting.
	 *
	 * @return void
	 */
	public function usort( callable $callback ): void {
		usort( $this->values, $callback );
	}

}
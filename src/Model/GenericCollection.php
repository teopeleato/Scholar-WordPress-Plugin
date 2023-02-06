<?php

namespace Model;

use ArrayIterator;
use IteratorAggregate;

abstract class GenericCollection implements IteratorAggregate {
	public static $itemClass;
	protected array $values = [];


	public function __construct( ...$values ) {
		$this->values = $values;
	}

	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->values );
	}

	public function count(): int {
		return count( $this->values );
	}

	public function add( ...$values ): void {
		foreach ( $values as $value ) {
			if ( ! $this->contains( $value ) ) {
				$this->values[] = $value;
			}
		}
	}

	public function contains( $value ): bool {
		return in_array( $value, $this->values, true );
	}

	public function remove( ...$values ): void {
		foreach ( $values as $value ) {
			$key = array_search( $value, $this->values, true );
			if ( false !== $key ) {
				unset( $this->values[ $key ] );
			}
		}
	}

	public function clear(): void {
		$this->values = [];
	}

	public function isEmpty(): bool {
		return empty( $this->values );
	}

	public function first() {
		return reset( $this->values );
	}

	public function last() {
		return end( $this->values );
	}

	public function get( int $key ) {
		return $this->values[ $key ] ?? null;
	}

	public function set( int $key, $value ): void {
		$this->values[ $key ] = $value;
	}

	public function values(): array {
		return array_values( $this->values );
	}

	public function usort( callable $callback ): void {
		usort( $this->values, $callback );
	}

}
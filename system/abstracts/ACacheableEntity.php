<?php

namespace system\abstracts;

use Exception;

abstract class ACacheableEntity extends AEntity {

    public string $created;
    public ?string $updated;
    public ?string $deleted;

	/**
	 * returns the timestamp of the last modification
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	abstract public static function getLastModification(): int;

	/**
	 * @return bool
	 */
	public static function isCacheable(): bool {
		return true;
	}
}
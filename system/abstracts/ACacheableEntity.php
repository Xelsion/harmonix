<?php

namespace system\abstracts;

abstract class ACacheableEntity extends AEntity {

    public string $created;
    public ?string $updated;
    public ?string $deleted;

	/**
	 * @return bool
	 */
	public static function isCacheable(): bool {
		return true;
	}
}
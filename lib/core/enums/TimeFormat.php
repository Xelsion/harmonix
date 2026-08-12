<?php

namespace lib\core\enums;

enum TimeFormat {

	public const int NS_TO_MICRO = 1_000;

	public const int NS_TO_MILLI = 1_000_000;

	public const int NS_TO_SEC = 1_000_000_000;

	public const int NS_TO_MIN = 60_000_000_000;

	public const int NS_TO_HOUR = 3_600_000_000_000;

	case NANO;
	case MICRO;
	case MILLI;
	case SEC;
	case MIN;
	case HOURS;

	/**
	 * Returns the SystemMessageType as string
	 *
	 * @return string
	 */
	public function toString(): string {
		return match ($this) {
			self::NANO => "ns",
			self::MICRO => "µs",
			self::MILLI => "ms",
			self::SEC => "s",
			self::MIN => "m",
			self::HOURS => "h"
		};
	}

	/**
	 * Returns the SystemMessageType as string
	 *
	 * @return int
	 */
	public function getDivider(): int {
		return match ($this) {
			self::NANO => 1,
			self::MICRO => self::NS_TO_MICRO,
			self::MILLI => self::NS_TO_MILLI,
			self::SEC => self::NS_TO_SEC,
			self::MIN => self::NS_TO_MIN,
			self::HOURS => self::NS_TO_HOUR
		};
	}
}

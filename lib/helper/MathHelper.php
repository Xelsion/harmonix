<?php

namespace lib\helper;

readonly class MathHelper {

	// =========================================================================
	// DEFAULT CALCULATIONS
	// =========================================================================

	/**
	 * Returns the correct rounded currency as float from the given value
	 *
	 * @param int|float|string $value
	 * @return float
	 */
	public static function getRoundedCurrency(int|float|string $value): float {
		if( is_string($value) ) {
			// if both , and . (eg. 1.235,23 oder 1,235.23)
			if( str_contains($value, ',') && str_contains($value, '.') ) {
				if( strpos($value, ',') < strpos($value, '.') ) {
					// englisch format (1,235.23) -> remove the ','
					$value = str_replace(',', '', $value);
				} else {
					// german format (1.235,23) -> remove the '.' and then replace ',' to '.'
					$value = str_replace(array('.', ','), array('', '.'), $value);
				}
			} else {
				// only one separator (eg. 1235,23)
				$value = str_replace(',', '.', $value);
			}
			$value = (float)$value;
		}
		return round($value * 100) / 100;
	}

	/**
	 * Returns the percentage of a value compared to a total
	 *
	 * @param int|float $value
	 * @param int|float $total
	 * @return float
	 */
	public static function getPercentage(int|float $value, int|float $total): float {
		$value = ($total > 0) ? ($value / $total) * 100 : 0;
		return round($value * 100) / 100;
	}

	/**
	 * Calculates the new dimensions for an image while preserving its aspect ratio.
	 *
	 * @param int $srcWidth Original image width.
	 * @param int $srcHeight Original image height.
	 * @param int $maxWidth Maximum allowed width.
	 * @param int $maxHeight Maximum allowed height.
	 * @return array Array with ['width' => int, 'height' => int]
	 */
	public static function scaleAspectRatio(int $srcWidth, int $srcHeight, int $maxWidth, int $maxHeight): array {
		$ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);
		// If the image is already smaller than the max dimensions, don't upscale
		if( $ratio > 1 ) {
			return ['width' => $srcWidth, 'height' => $srcHeight];
		}
		return [
			'width'  => (int)round($srcWidth * $ratio),
			'height' => (int)round($srcHeight * $ratio)
		];
	}

	// =========================================================================
	// WCAG
	// =========================================================================

	/**
	 * Calculates the relative luminance of an RGB/Hex color based on WCAG 2.0.
	 * Useful for checking if text is readable on a specific background color.
	 *
	 * @param string $hexColor Hex color code (e.g., "#FFFFFF" or "000000").
	 * @return float Luminance value between 0.0 (darkest black) and 1.0 (brightest white).
	 */
	public static function getRelativeLuminance(string $hexColor): float {
		$hex = str_replace('#', '', $hexColor);
		if( strlen($hex) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = hexdec(substr($hex, 0, 2)) / 255;
		$g = hexdec(substr($hex, 2, 2)) / 255;
		$b = hexdec(substr($hex, 4, 2)) / 255;

		// WCAG formula for sRGB
		$calc = static function($v) {
			return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
		};

		return 0.2126 * $calc($r) + 0.7152 * $calc($g) + 0.0722 * $calc($b);
	}

	/**
	 * Calculates the contrast ratio between two hex colors based on WCAG 2.0.
	 *
	 * @param string $hexColor1 First color (e.g., Background "#FFFFFF")
	 * @param string $hexColor2 Second color (e.g., Text "#000000")
	 * @return float Contrast ratio between 1.0 and 21.0 (expressed as X:1)
	 */
	public static function getContrastRatio(string $hexColor1, string $hexColor2): float {
		$lum1 = self::getRelativeLuminance($hexColor1);
		$lum2 = self::getRelativeLuminance($hexColor2);

		// WCAG formula: (L1 + 0.05) / (L2 + 0.05) where L1 is the lighter luminance
		$max = max($lum1, $lum2);
		$min = min($lum1, $lum2);

		return round(($max + 0.05) / ($min + 0.05), 2);
	}

	/**
	 * Determines whether black or white text has a better contrast on a given background color.
	 * Highly useful for dynamic UI components (like badges, buttons, or banners).
	 *
	 * @param string $backgroundColor The hex code of the background.
	 * @param string $darkText Hex code for the dark text option (Default: "#000000").
	 * @param string $lightText Hex code for the light text option (Default: "#FFFFFF").
	 * @return string The hex code that provides the highest contrast.
	 */
	public static function getReadableTextColor(string $backgroundColor, string $darkText = '#000000', string $lightText = '#FFFFFF'): string {
		$contrastWithDark = self::getContrastRatio($backgroundColor, $darkText);
		$contrastWithLight = self::getContrastRatio($backgroundColor, $lightText);

		return $contrastWithDark > $contrastWithLight ? $darkText : $lightText;
	}

	// =========================================================================
	// FINANCE & TAXATION (Commercial Rounding)
	// =========================================================================

	/**
	 * Calculates the gross value (including tax) from a net value.
	 * Example: Net 100.00 + 19% Tax = 119.00
	 *
	 * @param int|float $value The net amount before tax.
	 * @param int $percent The tax rate percentage (e.g., 19 for 19%).
	 * @return float Commercial rounded gross value.
	 */
	public static function getBruttoOf(int|float $value, int $percent): float {
		$brutto = $value * (1 + ($percent / 100));
		return round($brutto, 2, PHP_ROUND_HALF_UP);
	}

	/**
	 * Calculates the net value (excluding tax) from a gross value.
	 * Example: Gross 119.00 with 19% Tax = 100.00
	 *
	 * @param int|float $value The gross amount including tax.
	 * @param int $percent The tax rate percentage (e.g., 19 for 19%).
	 * @return float Commercial rounded net value.
	 */
	public static function getNettoOf(int|float $value, int $percent): float {
		$netto = $value / (1 + ($percent / 100));
		return round($netto, 2, PHP_ROUND_HALF_UP);
	}

	/**
	 *  Calculates the pure tax amount to be added to a net value.
	 *  Example: Net 100.00 with 19% Tax = 19.00
	 *
	 * @param int|float $value The net amount before tax.
	 * @param int $percent The tax rate percentage (e.g., 19 for 19%).
	 * @return float Commercial rounded tax portion.
	 */
	public static function getTexFromNetto(int|float $value, int $percent): float {
		$tex_value = $value * ($percent / 100);
		return round($tex_value, 2, PHP_ROUND_HALF_UP);
	}

	/**
	 * Extracts the pure tax amount contained within a gross value.
	 * Example: Gross 119.00 with 19% Tax = 19.00
	 *
	 * @param int|float $value The gross amount including tax.
	 * @param int $percent The tax rate percentage (e.g., 19 for 19%).
	 * @return float Commercial rounded tax amount.
	 */
	public static function getTexFromBrutto(int|float $value, int $percent): float {
		$tex_value = $value * ($percent / ($percent + 100));
		return round($tex_value, 2, PHP_ROUND_HALF_UP);
	}

	// =========================================================================
	// 2. GEOGRAPHY & DISTANCES (Spherical Law of Cosines)
	// =========================================================================

	/**
	 * Calculates the great-circle distance between two coordinates.
	 *
	 * @param float $lat1 Latitude of the start point in degrees.
	 * @param float $lon1 Longitude of the start point in degrees.
	 * @param float $lat2 Latitude of the destination point in degrees.
	 * @param float $lon2 Longitude of the destination point in degrees.
	 * @param float $radius Radius of the planet (Default: 6371.0 km for Earth).
	 * @return float Distance in the same unit as the earth radius (kilometers).
	 */
	public static function getGeoDistance(float $lat1, float $lon1, float $lat2, float $lon2, float $radius = 6378.38): float {
		$rLat1 = deg2rad($lat1);
		$rLon1 = deg2rad($lon1);
		$rLat2 = deg2rad($lat2);
		$rLon2 = deg2rad($lon2);

		$sinLat = sin($rLat1) * sin($rLat2);
		$cosLat = cos($rLat1) * cos($rLat2);
		$cosLon = cos($rLon2 - $rLon1);

		// Clamping prevents float precision errors from exceeding 1.0 (which crashes acos)
		$cosValue = max(-1.0, min(1.0, $sinLat + ($cosLat * $cosLon)));
		return $radius * acos($cosValue);
	}

	// =========================================================================
	// 3. UI, ANIMATION & DATA MAPPING
	// =========================================================================

	/**
	 * Re-maps a number from one range to another proportionally (Extended rule of three).
	 * Example: A sensor gives 512 (range 0-1024). Mapped to percentage (0-100) -> returns 50.0
	 *
	 * @param float $value The incoming value to be mapped.
	 * @param float $low1 Lower bound of the value's current range.
	 * @param float $high1 Upper bound of the value's current range.
	 * @param float $low2 Lower bound of the target range.
	 * @param float $high2 Upper bound of the target range.
	 * @return float The mapped value.
	 */
	public static function mapRange(float $value, float $low1, float $high1, float $low2, float $high2): float {
		// Protection against division by zero
		if( $high1 - $low1 === 0.0 ) {
			return $low2;
		}
		return $low2 + ($value - $low1) * ($high2 - $low2) / ($high1 - $low1);
	}

	/**
	 * Linear Interpolation (Lerp). Returns a value between start and end based on percentage t.
	 * Frequently used for animations, UI transitions, or smooth scrolling.
	 *
	 * @param float $start The starting value (at t = 0.0).
	 * @param float $end The ending value (at t = 1.0).
	 * @param float $t The interpolation factor, ideally between 0.0 and 1.0.
	 * @return float The interpolated value.
	 */
	public static function lerp(float $start, float $end, float $t): float {
		return $start + $t * ($end - $start);
	}

}

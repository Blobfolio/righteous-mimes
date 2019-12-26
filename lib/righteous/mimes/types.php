<?php
/**
 * Righteous MIMES: Types
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\MIMEs;

use Righteous\MIMEs;
use Righteous\MIMEs\Data;
use Righteous\MIMEs\Sanitize;



final class Types {
	/**
	 * Ambiguate Type
	 *
	 * This converts a type/subtype into a combination of type/subtype,
	 * type/x-subtype, etc., for look-up convenience. Most or all of the
	 * results will be wrong.
	 *
	 * @param string $type Type.
	 * @return ?array Types.
	 */
	public static function ambiguate(string $type) : ?array {
		// Easy abort.
		if (
			null === ($first = Sanitize::type($type, MIMEs::FILTER_NO_DEFAULT | MIMEs::FILTER_NO_EMPTY))
		) {
			return null;
		}

		list($type, $subtype) = \explode('/', $first);

		// Start with three obvious choices.
		$subtype = \preg_replace('/^(x\-|vnd\.)/', '', $subtype);
		$out = array(
			"$type/$subtype",
			"$type/x-$subtype",
			"$type/vnd.$subtype",
		);

		// Fonts have historically lived in either of two places, giving
		// us twice as many sources to check.
		if ('font' === $type) {
			$out[] = "application/font-$subtype";
			$out[] = "application/x-font-$subtype";
			$out[] = "application/vnd.font-$subtype";
		}
		// Equal and opposite to the above.
		elseif (0 === \strpos($subtype, 'font-')) {
			$font = \substr($subtype, 5);
			$out[] = "font/$font";
			$out[] = "font/x-$font";
			$out[] = "font/vnd.$font";
		}
		// Make office searching easier.
		elseif (0 === \strpos($out[0], 'application/cdfv2')) {
			$out[] = MIMEs::OFFICE_TYPE;
		}

		// Put the original first.
		$out = \array_diff(\array_unique($out), array($first));
		\array_unshift($out, $first);

		return \array_values($out);
	}

	/**
	 * Primary Extension by Type
	 *
	 * @param string $type Type.
	 * @return ?array Extensions.
	 */
	public static function extension(string $type) : ?string {
		if (null !== ($tmp = self::extensions($type))) {
			return \array_key_first($tmp);
		}

		return null;
	}

	/**
	 * Extensions by Type
	 *
	 * @param string $type Type.
	 * @return ?array Extensions.
	 */
	public static function extensions(string $type) : ?array {
		if (
			null !== ($type = Sanitize::type($type,
				MIMEs::FILTER_NO_DEFAULT |
				MIMEs::FILTER_NO_EMPTY |
				MIMEs::FILTER_NO_UNKNOWN |
				MIMEs::FILTER_UPDATE_ALIAS
			)) &&
			isset(Data\Types::TYPES[$type])
		) {
			return Data\Types::TYPES[$type];
		}
	}

	/**
	 * Is A/V Alias
	 *
	 * Determine whether two subtypes of audio/video types are
	 * identical.
	 *
	 * @param string $type1 Type one.
	 * @param string $type2 Type two.
	 * @return bool True/false.
	 */
	public static function is_av_alias(string $type1, string $type2) : bool {
		// Dig deeper if we can.
		if (
			(null !== ($type1 = Sanitize::type($type1))) &&
			(null !== ($type2 = Sanitize::type($type2)))
		) {
			// Easy abort.
			if ($type1 === $type2) {
				return true;
			}

			// Split them up.
			list($a_type, $a_subtype) = \explode('/', $type1);
			list($b_type, $b_subtype) = \explode('/', $type2);

			// Allow a shift provided the main types are both audio/video.
			if (
				($a_subtype === $b_subtype) &&
				('audio' === $a_type || 'video' === $a_type) &&
				('audio' === $b_type || 'video' === $b_type)
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is Office Type?
	 *
	 * @param string $type Type.
	 * @return bool True/false.
	 */
	public static function is_office_alias(string $type) : bool {
		if (null === ($types = self::ambiguate($type))) {
			return false;
		}

		return ! empty(\array_intersect($types, Data\Aliases::OFFICE));
	}

	/**
	 * Primary Type
	 *
	 * Upgrade an alias to a primary type.
	 *
	 * @param string $type Type.
	 * @return ?string Type.
	 */
	public static function primary_type(string $type) : ?string {
		// We'll check all the possible aliases, just in case.
		if (null === ($types = self::ambiguate($type))) {
			return null;
		}

		// Try it as-is.
		foreach ($types as $type) {
			if (isset(Data\Aliases::ALIASES[$type])) {
				return Data\Aliases::ALIASES[$type];
			}
		}

		return null;
	}
}

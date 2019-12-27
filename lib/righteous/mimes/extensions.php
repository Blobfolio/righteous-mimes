<?php
/**
 * Righteous MIMES: Extensions
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\MIMEs;

use Righteous\MIMEs;
use Righteous\MIMEs\Data;
use Righteous\MIMEs\Sanitize;



final class Extensions {
	/**
	 * Ambiguate Extension Types
	 *
	 * Pull in every possible type match for a given extension. Most of
	 * these will be undesirable, but weird things happy.
	 *
	 * @param string $ext Extension.
	 * @return ?array Types.
	 */
	public static function ambiguate(string $ext) : ?array {
		if (null === ($ext = Sanitize::extension($ext, MIMEs::FILTER_NO_UNKNOWN))) {
			return null;
		}

		// Start with the obvious ones.
		$out = isset(Data\Extensions::TYPES[$ext]) ? \array_keys(Data\Extensions::TYPES[$ext]) : array();

		// If we're literally looking for "gz" or "zip" — common parent
		// types of unique construction — we're done.
		if ('gz' === $ext || 'zip' === $ext) {
			\sort($out);
			return $out;
		}

		$groups = self::group($ext);
		if (0 < $groups) {
			if (MIMEs::GROUP_GZIP & $groups) {
				$out = \array_merge(
					$out,
					\array_keys(Data\Extensions::TYPES['gz'])
				);
			}
			if (MIMEs::GROUP_JSON & $groups) {
				$out = \array_merge(
					$out,
					\array_keys(Data\Extensions::TYPES['json'])
				);
			}
			if (MIMEs::GROUP_TEXT & $groups) {
				$out[] = 'text/plain';
			}
			if (MIMEs::GROUP_XML & $groups) {
				$out = \array_merge(
					$out,
					\array_keys(Data\Extensions::TYPES['xml'])
				);
			}
			if (MIMEs::GROUP_ZIP & $groups) {
				$out[] = 'application/zip';
				$out[] = MIMEs::TYPE_DEFAULT;
			}
		}

		// Sort and return.
		$out = \array_unique($out);
		\sort($out);
		return $out;
	}

	/**
	 * Get Group(s)
	 *
	 * If this extension's type is made up of content from another type,
	 * note that here.
	 *
	 * @param string $ext Extension.
	 * @return int Groups.
	 */
	public static function group(string $ext) : int {
		if (
			(null !== ($ext = Sanitize::extension($ext))) &&
			isset(Data\Extensions::GROUPS[$ext])
		) {
			return Data\Extensions::GROUPS[$ext];
		}

		return 0;
	}

	/**
	 * Primary Type
	 *
	 * Return the primary type for a given extension.
	 *
	 * @param string $ext Extension.
	 * @return ?string Type.
	 */
	public static function primary_type(string $ext) : ?string {
		if (null !== ($ext = Sanitize::extension($ext, MIMEs::FILTER_NO_UNKNOWN))) {
			return \array_key_first(Data\Extensions::TYPES[$ext]);
		}

		return null;
	}

	/**
	 * Get Source(s)
	 *
	 * @param string $ext Extension.
	 * @param string $type Type.
	 * @return int Source.
	 */
	public static function source(string $ext, string $type) : int {
		if (null !== ($ext = Sanitize::extension($ext, MIMEs::FILTER_NO_UNKNOWN))) {
			return Data\Extensions::TYPES[$ext][$type]['source'] ?? 0;
		}

		return 0;
	}

	/**
	 * Verify Type
	 *
	 * @param string $ext Extension.
	 * @param string $type Type.
	 * @return bool True/false.
	 */
	public static function verify_extension_type(string $ext, string $type) : bool {
		return (
			(null !== ($haystack = self::ambiguate($ext))) &&
			(null !== ($needles = Types::ambiguate($type))) &&
			! empty(\array_intersect($needles, $haystack))
		);
	}
}

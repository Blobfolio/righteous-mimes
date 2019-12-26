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

		// Now go through and add in parent types as needed.
		if (\in_array($ext, Data\Extensions::GZIP, true)) {
			$out = \array_merge($out, \array_keys(Data\Extensions::TYPES['gz']));
		}
		if (\in_array($ext, Data\Extensions::JSON, true)) {
			$out = \array_merge($out, \array_keys(Data\Extensions::TYPES['json']));
		}
		if (\in_array($ext, Data\Extensions::TEXT, true)) {
			$out[] = 'text/plain';
		}
		if (\in_array($ext, Data\Extensions::XML, true)) {
			$out = \array_merge($out, \array_keys(Data\Extensions::TYPES['xml']));
		}
		if (\in_array($ext, Data\Extensions::ZIP, true)) {
			$out[] = 'application/zip';
			$out[] = MIMEs::TYPE_DEFAULT;
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
		if (null !== ($ext = Sanitize::extension($ext, MIMEs::FILTER_NO_UNKNOWN))) {
			$out = 0;

			if (\in_array($ext, Data\Extensions::GZIP, true)) {
				$out |= MIMEs::GROUP_GZIP;
			}
			if (\in_array($ext, Data\Extensions::JSON, true)) {
				$out |= MIMEs::GROUP_JSON;
			}
			if (\in_array($ext, Data\Extensions::OFFICE, true)) {
				$out |= MIMEs::GROUP_OFFICE;
			}
			if (\in_array($ext, Data\Extensions::TEXT, true)) {
				$out |= MIMEs::GROUP_TEXT;
			}
			if (\in_array($ext, Data\Extensions::XML, true)) {
				$out |= MIMEs::GROUP_XML;
			}
			if (\in_array($ext, Data\Extensions::ZIP, true)) {
				$out |= MIMEs::GROUP_ZIP;
			}

			return $out;
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

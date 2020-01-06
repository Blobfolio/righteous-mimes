<?php
/**
 * Righteous MIMES: Sanitize
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\MIMEs;

use Righteous\MIMEs;
use Righteous\MIMEs\Data;
use Righteous\MIMEs\Types;



final class Sanitize {
	/**
	 * File Extension
	 *
	 * @param string $ext File path, name, extension, etc.
	 * @param int $flags Flags.
	 * @return ?string Extension.
	 */
	public static function extension(string $ext, int $flags = 0) : ?string {
		// If the extension looks like a path, let's chop it.
		if (false !== \strpos($ext, '/') || false !== \strpos($ext, '\\')) {
			$ext = \basename($ext);
			if (
				! $ext ||
				(null === $ext = \preg_replace('/(^[\.\s]+|[\.\s]+$)/u', '', $ext)) ||
				false === \strpos($ext, '.')
			) {
				return null;
			}
		}

		// We might need to chop it to the last part.
		if (false !== ($end = \mb_strrpos($ext, '.', 0, 'UTF-8'))) {
			$ext = \mb_substr($ext, $end + 1, null, 'UTF-8');
		}

		// Clean it up.
		$ext = \preg_replace(
			array(
				// Alphanumeric with underscores and dashes.
				'/[^a-z\d_-]/u',
				// Trim the outside of special characters.
				'/^[_-]+/',
				'/[_-]+$/',
			),
			'',
			\strtolower($ext)
		);

		// We can do it!
		if (
			$ext &&
			(
				! (MIMEs::FILTER_NO_UNKNOWN & $flags) ||
				isset(Data\Extensions::TYPES[$ext])
			)
		) {
			return $ext;
		}

		return null;
	}

	/**
	 * File Type
	 *
	 * @param string $type MIME type.
	 * @param int $flags Flags.
	 * @return ?string Type.
	 */
	public static function type(string $type, int $flags = 0) : ?string {
		$type = \preg_replace(
			array(
				// Alphanumeric with + - .
				'#[^-\+\.a-z\d/]#u',
				// Collapse double dashes.
				'#\-{2,}#',
				// Collapse double pluses.
				'#\+{2,}#',
				// Collapse double dots.
				'#\.{2,}#',
				// Types and subtypes start and end alphanumerically.
				// For now, let's trim the outsides of special
				// characters.
				'#^[-\+\.]+#',
				'#[-\+\.]+$#',
			),
			array(
				'',
				'-',
				'+',
				'.',
				'',
				'',
			),
			\strtolower($type)
		) ?? '';

		// Early abort.
		if (
			! $type ||
			! \preg_match('#^([^/]+)/([^/]+)$#', $type, $matches) ||
			! isset($matches[2])
		) {
			return null;
		}

		// Now trim the insides of special characters.
		$type = \rtrim($matches[1], '-+.');
		$subtype = \ltrim($matches[2], '-+.');

		// Another early abort.
		if (! $type || ! $subtype) {
			return null;
		}

		// Rebuild it.
		$type = "$type/$subtype";

		// More aborts!
		if (
			(
				(MIMEs::FILTER_NO_DEFAULT & $flags) &&
				MIMEs::TYPE_DEFAULT === $type
			) ||
			((MIMEs::FILTER_NO_EMPTY & $flags) && MIMEs::TYPE_EMPTY === $type)
		) {
			return null;
		}

		// Try to upgrade the type.
		if (
			(MIMEs::FILTER_UPDATE_ALIAS & $flags) &&
			(null !== ($tmp = Types::primary_type($type)))
		) {
			return $tmp;
		}

		// If we're forbidding aliases, this has to match.
		if (MIMEs::FILTER_NO_ALIAS & $flags) {
			if (
				isset(Data\Aliases::ALIASES[$type]) &&
				Data\Aliases::ALIASES[$type] === $type
			) {
				return $type;
			}

			return null;
		}

		// If we're weeding out unknown data, we have to verify
		// first.
		if (MIMEs::FILTER_NO_UNKNOWN & $flags) {
			if (null !== ($types = Types::ambiguate($type))) {
				foreach ($types as $v) {
					if (
						isset(Data\Types::TYPES[$v]) ||
						isset(Data\Aliases::ALIASES[$v])
					) {
						return (MIMEs::FILTER_UPDATE_ALIAS & $flags) ? $v : $type;
					}
				}
			}

			return null;
		}

		return $type;
	}
}

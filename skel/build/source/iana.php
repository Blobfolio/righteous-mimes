<?php
/**
 * Righteous MIMES: IANA
 *
 * @see {https://www.iana.org/assignments/media-types}
 *
 * @copyright 2017 IETF Trust
 * @license https://www.rfc-editor.org/copyright/ rfc-copyright-story
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class Iana extends \Righteous\Build\Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_IANA;

	/**
	 * Patterns
	 */
	const PATTERNS = array(
		'/suffix is "([\da-z\-_]{2,})"/ui',
		'/saved with the the file suffix ([\da-z\-_]{2,})./ui',
		'/ files: \.([\da-z\-_]{2,})./ui',
		'/file extension\(s\):\v\s*\*?\.([\da-z\-_]{2,})/ui',
	);

	/**
	 * Shitlist
	 */
	const SHITLIST = array(
		'na',
		'none',
		'undefined',
		'unknown',
	);

	/**
	 * Types
	 */
	const TYPES = array(
		'application',
		'audio',
		'font',
		'image',
		'message',
		'model',
		'multipart',
		'text',
		'video',
	);

	/**
	 * Parse Raw Data
	 *
	 * Parse the raw source data, returning an array keyed like:
	 *    extension_1: [
	 *       type_1: (int) flags, …
	 *    ], …
	 *
	 * If there is an error and no data exists, return null.
	 *
	 * @param ?string $path Path to raw content.
	 * @return ?array Types by Extension.
	 */
	public static function parse(?string $path) : ?array {
		// The directory must exist.
		$path = \realpath($path);
		if (! $path || ! \is_dir($path)) {
			return null;
		}

		$out = array();

		// Each type has its own folder. Start by looping through those.
		foreach (self::TYPES as $category) {
			$files = \array_diff(
				\scandir("{$path}/{$category}"),
				array('..', '.')
			);
			if (empty($files)) {
				continue;
			}

			// Loop through the files in said folder.
			foreach ($files as $file) {
				$subtype = \basename($file);
				$type = "{$category}/{$subtype}";
				$content = \trim(\file_get_contents("{$path}/{$category}/{$file}"));
				if (! $content) {
					continue;
				}

				// Tease what we can from the basic patterns.
				foreach (self::PATTERNS as $pattern) {
					\preg_match($pattern, $content, $matches);
					if (isset($matches[1])) {
						static::_parse_pair($matches[1], $type, false, $out);
					}
				}

				// Now check to see if there's anything in the
				// extensions section.
				\preg_match_all(
					'/\s*file extension(\(s\))?\s*:\s*([\.,\da-z\h\-_]+)/ui',
					$content,
					$matches
				);
				if (empty($matches[2])) {
					continue;
				}

				// Clean up and compile into a list.
				$matches[2][0] = \trim(\preg_replace('/\s+/u', ' ', $matches[2][0]));
				$matches[2][0] = \str_replace(' or ', ',', $matches[2][0]);
				$raw = \explode(',', $matches[2][0]);

				// Run through each entry, saving as we go!
				foreach ($raw as $k=>$v) {
					$raw[$k] = \trim(\str_replace(array('.', '*'), '', \strtolower($v)));
					if (
						$raw[$k] &&
						\preg_match('/^[\da-z]+[\da-z\-_]*[\da-z]+$/', $raw[$k]) &&
						! \in_array($raw[$k], self::SHITLIST, true)
					) {
						static::_parse_pair($raw[$k], $type, false, $out);
					}
				}
			} // Each file.
		} // Each category.

		return static::_post_parse($out);
	}
}

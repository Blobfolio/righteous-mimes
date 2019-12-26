<?php
/**
 * Righteous MIMES: Apache
 *
 * @see {https://raw.githubusercontent.com/apache/httpd/trunk/docs/conf/mime.types}
 *
 * @copyright 2017 The Apache Software Foundation
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class Apache extends \Righteous\Build\Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_APACHE;

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
		// Pull the content.
		if (false === ($content = static::_read_lines($path))) {
			return null;
		}

		// Pull it apart.
		$out = array();
		foreach ($content as $line) {
			if (! $line || 0 === \strpos($line, '#')) {
				continue;
			}

			$line = \preg_replace('/\s+/u', ' ', $line);
			$line = \explode(' ', \trim($line));
			if (! isset($line[0], $line[1])) {
				continue;
			}

			$type = \strtolower($line[0]);
			unset($line[0]);
			foreach ($line as $ext) {
				static::_parse_pair($ext, $type, false, $out);
			}
		}

		return static::_post_parse($out);
	}
}

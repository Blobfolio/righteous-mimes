<?php
/**
 * Righteous MIMES: Apache
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class Blobfolio extends \Righteous\Build\Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_BLOBFOLIO;

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
		if (
			! $path ||
			! \is_file($path) ||
			(false === ($handle = \fopen($path, 'r')))
		) {
			return null;
		}

		$out = array();
		$num = 0;
		while ($line = \fgetcsv($handle)) {
			++$num;
			if (1 === $num || ! isset($line[2])) {
				continue;
			}

			$alias = (int) $line[2];
			static::_parse_pair($line[0], $line[1], !! $alias, $out);
		}
		\fclose($handle);

		return static::_post_parse($out);
	}
}

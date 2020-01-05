<?php
/**
 * Righteous MIMES: WordPress
 *
 * @see {https://raw.githubusercontent.com/WordPress/WordPress/master/wp-includes/functions.php}
 *
 * @copyright 2020 Automattic.
 * @license https://wordpress.org/about/license/ GPLv2
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class WordPress extends \Righteous\Build\Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_WORDPRESS;

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
		$content = \file_get_contents($path);
		if (! $content) {
			return null;
		}

		// We need to parse the value out.
		if (! \preg_match("/return apply_filters\(\s*'mime_types',\s*/", $content, $match)) {
			return null;
		}
		if (false === $start = \mb_strpos($content, $match[0], 0, 'UTF-8')) {
			return null;
		}
		$content = \mb_substr(
			$content,
			$start + \strlen($match[0]),
			null,
			'UTF-8'
		);
		$content = '$raw = ' . \trim(\strstr($content, ');', true)) . ';';

		// phpcs:disable
		eval($content);
		// phpcs:enable

		// Do we have a value?
		if (empty($raw) || ! \is_array($raw)) {
			return null;
		}

		// Add the entries!
		$out = array();
		foreach ($raw as $exts=>$type) {
			$exts = \explode('|', $exts);
			foreach ($exts as $ext) {
				static::_parse_pair($ext, $type, false, $out);
			}
		}

		return static::_post_parse($out);
	}
}

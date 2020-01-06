<?php
/**
 * Righteous MIMES: Drupal
 *
 * @see {https://raw.githubusercontent.com/drupal/drupal/8.8.x/core/lib/Drupal/Core/File/MimeType/ExtensionMimeTypeGuesser.php}
 *
 * @copyright 2020 Drupal.
 * @license https://www.drupal.org/about/licensing GPL
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class Drupal extends \Righteous\Build\Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_DRUPAL;

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
		if (false === $start = \mb_strpos($content, '$defaultMapping =', 0, 'UTF-8')) {
			return null;
		}
		$content = \mb_substr(
			$content,
			$start,
			null,
			'UTF-8'
		);
		$content = \trim(\strstr($content, '];', true)) . '];';
		$content = \str_replace('$defaultMapping', '$raw', $content);

		// phpcs:disable
		eval($content);
		// phpcs:enable

		// Do we have a value?
		if (! isset($raw['mimetypes'], $raw['extensions'])) {
			return null;
		}

		// Add the entries!
		$out = array();
		foreach ($raw['extensions'] as $ext=>$type) {
			$type = $raw['mimetypes'][$type];
			static::_parse_pair($ext, $type, false, $out);
		}

		return static::_post_parse($out);
	}
}

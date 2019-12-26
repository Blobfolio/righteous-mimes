<?php
/**
 * Righteous MIMES: Data Crunching
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build;

use Righteous\MIMEs;
use Righteous\MIMEs\Sanitize;



abstract class Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = 0;

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
	abstract public static function parse(?string $path) : ?array;



	// -----------------------------------------------------------------
	// Internal.
	// -----------------------------------------------------------------

	/**
	 * Parse Pair
	 *
	 * @param string $ext Extension.
	 * @param string $type Type.
	 * @param bool $alias Alias.
	 * @param array $data Collection.
	 * @return void Nothing.
	 */
	protected static function _parse_pair(
		string $ext,
		string $type,
		bool $alias,
		array &$data
	) : void {
		if (
			(null === ($ext = Sanitize::extension($ext))) ||
			(null === ($type = Sanitize::type($type)))
		) {
			return;
		}

		if (! isset($data[$ext])) {
			$data[$ext] = array();
		}

		$data[$ext][$type] = static::FLAG;
		if ($alias) {
			$data[$ext][$type] |= MIMEs::SOURCE_ALIAS;
		}
	}

	/**
	 * File to Lines
	 *
	 * Return a file as an array of non-empty lines.
	 *
	 * @param string $file File path.
	 * @return ?array Content.
	 */
	protected static function _read_lines(string $file) : ?array {
		if (! \is_file($file)) {
			return null;
		}

		$content = \file_get_contents($file);
		$content = \str_replace("\r\n", "\n", $content);
		$content = \preg_replace('/\v+/u', "\n", $content);
		$content = \explode("\n", \trim($content));

		// Early bail.
		if (empty($content)) {
			return null;
		}

		// Trim and remove empty lines.
		foreach ($content as $k=>$v) {
			$content[$k] = \trim(\preg_replace('/(^\s+|\s+$)/u', '', \trim($v)));
			if (! $content[$k]) {
				unset($content[$k]);
			}
		}

		return empty($content) ? null : $content;
	}

	/**
	 * Read XML
	 *
	 * Return a SimpleXML object from the file content.
	 *
	 * @param string $file File path.
	 * @return ?object Content.
	 */
	protected static function _read_xml(string $file) : ?object {
		if (! \is_file($file)) {
			return null;
		}

		$content = \file_get_contents($file);

		// Tika's XML format crashes SimpleXML, so let's patch the bad
		// parts out.
		$content = \preg_replace(
			'/<tika:(link|uti)>(.*)<\/tika:(link|uti)>/Us',
			'',
			\trim($content)
		);

		return $content ? \simplexml_load_string($content) : null;
	}

	/**
	 * Send Response
	 *
	 * This cleans up the data and returns an appropriately formated
	 * result.
	 *
	 * @param array $result Result.
	 * @return ?array Result.
	 */
	protected static function _post_parse(array $result) : ?array {
		if (! empty($result)) {
			$reverse = array();

			// Make sure we don't have any empty entries.
			foreach ($result as $k=>$v) {
				if (empty($v)) {
					unset($result[$k]);
				}
			}

			// We're done!
			if (! empty($result)) {
				\ksort($result);
				return $result;
			}
		}

		return null;
	}
}

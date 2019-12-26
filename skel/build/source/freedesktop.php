<?php
/**
 * Righteous MIMES: FreeDesktop.org
 *
 * @see {https://cgit.freedesktop.org/xdg/shared-mime-info/plain/freedesktop.org.xml.in}
 *
 * @copyright 2017 Freedesktop.org
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class FreeDesktop extends \Righteous\Build\Source {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_FREEDESKTOP;

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
		if (false === ($content = static::_read_xml($path))) {
			return null;
		}

		// Pull it apart.
		$out = array();
		foreach ($content as $type) {
			$types = array();

			// Find the types first.
			foreach ($type->attributes() as $k=>$v) {
				$v = \strtolower(\trim(\strval($v)));
				if ($v && 'type' === $k) {
					$types[$v] = false;
				}
			}

			// Explicit aliases.
			if (isset($type->alias)) {
				foreach ($type->alias as $alias) {
					foreach ($alias->attributes() as $k=>$v) {
						$v = \strtolower(\trim(\strval($v)));
						if ($v && 'type' === $k) {
							$types[$v] = true;
						}
					}
				}
			}

			// Include parents as aliases as well.
			if (isset($type->{'sub-class-of'})) {
				foreach ($type->{'sub-class-of'}->attributes() as $k=>$v) {
					$v = \strtolower(\trim(\strval($v)));
					if (('type' === $k) && (false === \strpos($v, '/x-tika'))) {
						$types[$v] = true;
					}
				}
			}

			// Now we just need to tease the corresponding file
			// extensions from their glob format.
			$exts = array();
			if (isset($type->glob)) {
				foreach ($type->glob as $glob) {
					foreach ($glob->attributes() as $k=>$v) {
						$v = \strtolower(\trim(\strval($v)));
						if ('pattern' === $k) {
							$v = \ltrim($v, '.*');
							if (\preg_match('/^[\da-z]+[\da-z\-\_]*[\da-z]+$/', $v)) {
								$exts[] = $v;
							}
						}
					}
				}
			}

			// Loop and save!
			if (! empty($exts) && ! empty($types)) {
				foreach ($exts as $ext) {
					foreach ($types as $type=>$alias) {
						static::_parse_pair($ext, $type, false, $out);
					}
				}
			}
		}

		return static::_post_parse($out);
	}
}

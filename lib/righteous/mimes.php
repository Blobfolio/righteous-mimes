<?php
/**
 * Righteous MIMES
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous;

final class MIMEs {
	// -----------------------------------------------------------------
	// File
	// -----------------------------------------------------------------

	/**
	 * File Empty
	 *
	 * The file exists locally but is 0 bytes.
	 */
	const FILE_EMPTY = 1;

	/**
	 * File Exists
	 *
	 * The file exists locally.
	 */
	const FILE_EXISTS = 2;

	/**
	 * Extension/Type Valid
	 *
	 * The file extension makes sense given the type.
	 */
	const FILE_NAME_VALID = 4;

	/**
	 * File Readable
	 *
	 * The file exists locally and is readable.
	 */
	const FILE_READABLE = 8;

	/**
	 * From Magic
	 *
	 * The file type information came, at least in part, after looking
	 * at its content.
	 *
	 * The "in part" part is important as a lot of what `fileinfo.so`
	 * does is naive in nature. This is an encouraging result, but not
	 * necessarily an All Clear.
	 */
	const FILE_VALIDITY_MAGIC = 16;

	/**
	 * From Naive
	 *
	 * The file type information came entirely from the file's name.
	 * It might be fine. It probably is. But maybe not.
	 */
	const FILE_VALIDITY_NAIVE = 32;

	/**
	 * From Unknown
	 *
	 * The library was unable to figure out what kind of file this is by
	 * any means. It could still be a totally valid file, just not one
	 * we or PHP or the underlying operating system understand at all.
	 */
	const FILE_VALIDITY_UNKNOWN = 64;



	// -----------------------------------------------------------------
	// Filter
	// -----------------------------------------------------------------

	/**
	 * Filter: No Alias
	 *
	 * When sanitizing a media type, reject results that are aliases.
	 * This implies FILTER_NO_UNKNOWN and can be combined with
	 * FILTER_UPDATE_ALIAS.
	 */
	const FILTER_NO_ALIAS = 1;

	/**
	 * Filter: No Default
	 *
	 * For reasons that are unclear, `fileinfo.so` often returns a type
	 * of "application/octet-stream" when it can't figure out what it's
	 * looking at.
	 *
	 * The unfortunate thing is "application/octet-stream" *is* a valid
	 * type sometimes, so we may not always want to accept it as an
	 * answer.
	 */
	const FILTER_NO_DEFAULT = 2;

	/**
	 * Filter: No Empty
	 *
	 * Empty files technically don't have a type, but some systems will
	 * return a made-up "inode/x-empty" value in such cases. In such
	 * cases magic doesn't really apply.
	 */
	const FILTER_NO_EMPTY = 4;

	/**
	 * Filter: No Unknown
	 *
	 * If this library has no information about a given extension or
	 * media type, there's not much we can really do with it.
	 */
	const FILTER_NO_UNKNOWN = 8;

	/**
	 * Filter: Update Alias
	 *
	 * When sanitizing a media type, try to upgrade it to a less stupid
	 * value. For example, we never want "image/x-bmp"; the correct type
	 * is "image/bmp".
	 */
	const FILTER_UPDATE_ALIAS = 16;



	// -----------------------------------------------------------------
	// Group Constants
	// -----------------------------------------------------------------

	const GROUP_GZIP = 1;
	const GROUP_JSON = 2;
	const GROUP_OFFICE = 4;
	const GROUP_TEXT = 8;
	const GROUP_XML = 16;
	const GROUP_ZIP = 32;



	// -----------------------------------------------------------------
	// Source Constants
	// -----------------------------------------------------------------

	/**
	 * Source Reported Alias
	 *
	 * There is an authoritative source record for a given type/ext, but
	 * that source reported the match as an alias.
	 */
	const SOURCE_ALIAS = 1;

	/**
	 * Shared Reference
	 *
	 * There is an authoritative source record for a given type/ext, but
	 * it is not unique so is hard to say which is *the* best.
	 */
	const SOURCE_SHARED = 2;

	/**
	 * Individual Sources
	 */
	const SOURCE_APACHE = 4;
	const SOURCE_BLOBFOLIO = 8;
	const SOURCE_FREEDESKTOP = 16;
	const SOURCE_IANA = 32;
	const SOURCE_NGINX = 64;
	const SOURCE_TIKA = 128;
	const SOURCE_WORDPRESS = 256;

	/**
	 * All Sources
	 */
	const SOURCE_CERTAIN = MIMEs::SOURCE_APACHE |
		MIMEs::SOURCE_BLOBFOLIO |
		MIMEs::SOURCE_FREEDESKTOP |
		MIMEs::SOURCE_IANA |
		MIMEs::SOURCE_NGINX |
		MIMEs::SOURCE_TIKA |
		MIMEs::SOURCE_WORDPRESS;



	// -----------------------------------------------------------------
	// Type Constants
	// -----------------------------------------------------------------

	/**
	 * Default MIME Type
	 *
	 * If `fileinfo.so` can't figure out what kind of file a binary is,
	 * this value is often returned as a sort of default.
	 */
	const TYPE_DEFAULT = 'application/octet-stream';

	/**
	 * Empty MIME Type
	 *
	 * If a file is empty and there are no name-based clues
	 * `fileinfo.so` can use, this value is often returned.
	 */
	const TYPE_EMPTY = 'inode/x-empty';

	/**
	 * Office MIME Type
	 *
	 * This is a generic MIME type often returned for generic MS Office
	 * documents that can't be classified any better.
	 */
	const TYPE_OFFICE = 'application/vnd.ms-office';



	// -----------------------------------------------------------------
	// Misc Methods
	// -----------------------------------------------------------------

	/**
	 * Authority
	 *
	 * Convert the source information into a numerical authority score
	 * for e.g. comparison/sorting purposes.
	 *
	 * Aliases count for nothing. Shared scores are graded equally
	 * regardless of source. Otherwise Blobfolio takes priority,
	 * followed by IANA, followed by everyone else.
	 *
	 * @param int $source Source.
	 * @return int Score.
	 */
	public static function source_authority(int $source) : int {
		$score = 0;

		// Nothing.
		if (0 >= $source || (self::SOURCE_ALIAS & $source)) {
			return $score;
		}

		// Start with the little guys.
		if (self::SOURCE_APACHE & $source) {
			++$score;
		}
		if (self::SOURCE_FREEDESKTOP & $source) {
			++$score;
		}
		if (self::SOURCE_NGINX & $source) {
			++$score;
		}
		if (self::SOURCE_TIKA & $source) {
			++$score;
		}
		if (self::SOURCE_WORDPRESS & $source) {
			++$score;
		}

		// Now the weighted guys.
		$shared = (bool) (self::SOURCE_SHARED & $source);
		if (self::SOURCE_IANA & $source) {
			$score += $shared ? 1 : 10;
		}
		if (self::SOURCE_BLOBFOLIO & $source) {
			$score += $shared ? 1 : 20;
		}

		return $score;
	}
}

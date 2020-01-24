<?php
/**
 * Righteous MIMES: File
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\MIMEs;

use getID3;
use Righteous\MIMEs;
use Righteous\MIMEs\Extensions;
use Righteous\MIMEs\Sanitize;



final class File {
	/**
	 * Template
	 */
	const TEMPLATE = array(
		'raw'=>'',
		'path'=>'',
		'dirname'=>'',
		'basename'=>'',
		'filename'=>'',
		'extension'=>'',
		'type'=>'',
		'flags'=>0,
		'naive_ext'=>'',
		'naive_type'=>'',
		'naive_group'=>0,
		'naive_source'=>0,
		'magic_ext'=>'',
		'magic_type'=>'',
		'magic_group'=>0,
		'magic_source'=>0,
		'magic_suggested'=>null,
	);

	/**
	 * Raw File Path
	 *
	 * For better or worse, this is the value used to initialize the
	 * File object.
	 *
	 * @var ?string
	 */
	protected $_file;



	// -----------------------------------------------------------------
	// Construction
	// -----------------------------------------------------------------

	/**
	 * Construct
	 *
	 * Evaluate a file path to see what we can tell about it.
	 *
	 * @param string $file File.
	 * @return bool True/false.
	 */
	public function __construct(string $file) {
		$this->_file = self::TEMPLATE;
		$this->_file['raw'] = \trim($file);

		// Early abort.
		if (! $this->_file['raw'] || ! $this->_set_naive()) {
			$this->_file = null;
			return false;
		}

		// Pull magic info if we have a valid, readable file (that isn't
		// totally empty).
		if (
			(MIMEs::FILE_EXISTS & $this->_file['flags']) &&
			(MIMEs::FILE_READABLE & $this->_file['flags']) &&
			! (MIMEs::FILE_EMPTY & $this->_file['flags'])
		) {
			$this->_set_magic();
		}

		// If we don't have any kind of type, we can make a quick exit.
		if (! $this->_file['naive_type'] && ! $this->_file['magic_type']) {
			// If we don't have an extension either, we're done.
			if (! $this->_file['naive_ext']) {
				$this->_file = null;
				return false;
			}

			$this->_file['extension'] = $this->_file['naive_ext'];
			$this->_file['flags'] |= MIMEs::FILE_VALIDITY_UNKNOWN;
			return true;
		}

		// Prefer magic if we have it.
		if ($this->_file['magic_type']) {
			$this->_file['type'] = $this->_file['magic_type'];
			$this->_file['extension'] = $this->_file['magic_ext'];

			if ($this->_file['extension'] === $this->_file['naive_ext']) {
				$this->_file['flags'] |= MIMEs::FILE_NAME_VALID;
			}

			$this->_file['flags'] |= MIMEs::FILE_VALIDITY_MAGIC;
			return true;
		}

		// Otherwise we stick with the naive.
		$this->_file['extension'] = $this->_file['naive_ext'];
		$this->_file['type'] = $this->_file['naive_type'];
		$this->_file['flags'] |= MIMEs::FILE_VALIDITY_NAIVE;
		if (Extensions::verify_extension_type($this->_file['extension'], $this->_file['type'])) {
			$this->_file['flags'] |= MIMEs::FILE_NAME_VALID;
		}

		return true;
	}

	/**
	 * Gather Naive Information
	 *
	 * @return bool True/false.
	 */
	protected function _set_naive() : bool {
		// If this looks like a URL, let's parse it as such. This isn't
		// what this function is for, really, so we won't try too hard.
		if (\preg_match('#^(https?|s?ftps?)?(:?//)#i', $this->_file['raw'])) {
			$this->_file['path'] = \parse_url($this->_file['raw'], \ PHP_URL_PATH) ?? '';
			if (! $this->_file['path']) {
				return false;
			}
		}
		// Directories ain't files.
		elseif (\is_dir($this->_file['raw'])) {
			return false;
		}
		// Let's fix the path if it is a file.
		elseif (\is_file($this->_file['raw'])) {
			$this->_file['path'] = \realpath($this->_file['raw']);
			// This shouldn't happen, but just in case...
			if (! $this->_file['path']) {
				return false;
			}

			$this->_file['flags'] |= MIMEs::FILE_EXISTS;

			// Check more about this file!
			if (\is_readable($this->_file['path'])) {
				$this->_file['flags'] |= MIMEs::FILE_READABLE;

				if (! \filesize($this->_file['path'])) {
					$this->_file['flags'] |= MIMEs::FILE_EMPTY;
				}
			}
		}
		else {
			$this->_file['path'] = \rtrim($this->_file['raw'], '/\\');
		}

		if (! (MIMEs::FILE_EXISTS & $this->_file['flags'])) {
			// We might need to chop queries and fragments if they've hung
			// around for some reason.
			if (false !== ($start = \mb_strpos($this->_file['path'], '?', 0, 'UTF-8'))) {
				$this->_file['path'] = \mb_substr(
					$this->_file['path'],
					0,
					$start,
					'UTF-8'
				);
				if (! $this->_file['path']) {
					return false;
				}
			}

			if (false !== ($start = \mb_strpos($this->_file['path'], '#', 0, 'UTF-8'))) {
				$this->_file['path'] = \mb_substr(
					$this->_file['path'],
					0,
					$start,
					'UTF-8'
				);
				if (! $this->_file['path']) {
					return false;
				}
			}
		}

		// Fill in a bunch of basic information.
		$this->_file['dirname'] = \dirname($this->_file['path']);
		$this->_file['basename'] = \basename($this->_file['path']);

		// Grab the extension our way. If this fails, we can't really
		// naively check anything else.
		if (null === ($this->_file['naive_ext'] = Sanitize::extension(
			$this->_file['path']
		))) {
			$this->_file['naive_ext'] = '';
			$this->_file['filename'] = $this->_file['basename'];
			return true;
		}

		// Get the group associations figured out.
		$this->_file['naive_group'] = Extensions::group($this->_file['naive_ext']);

		// We're using substrings here because unlike `pathinfo()`, this
		// library feels an extension must *follow* a file name in order
		// to *be* an extension.
		$this->_file['filename'] = \mb_substr(
			$this->_file['basename'],
			0,
			// Extensions must be alphanumeric so we can skip multibyte
			// worries.
			0 - 1 - \strlen($this->_file['naive_ext']),
			'UTF-8'
		);

		// Pull the type.
		if (null !== ($this->_file['naive_type'] = Extensions::primary_type(
			$this->_file['naive_ext']
		))) {
			// Pull the source.
			$this->_file['naive_source'] = Extensions::source(
				$this->_file['naive_ext'],
				$this->_file['naive_type']
			);
		}
		else {
			$this->_file['naive_type'] = '';
		}

		// We're done!
		return true;
	}

	/**
	 * Gather Magic Information
	 *
	 * @return bool True/false.
	 */
	protected function _set_magic() : bool {
		// Fileinfo is step one.
		$this->_file['magic_type'] = $this->_fileinfo() ?? '';
		$this->_fileinfo_context($this->_file['magic_type']);

		// Abort if we have no magic type, an empty magic type, or an
		// inappropriate default type.
		if (
			! $this->_file['magic_type'] ||
			(MIMEs::TYPE_EMPTY === $this->_file['magic_type']) ||
			(
				MIMEs::TYPE_DEFAULT === $this->_file['magic_type'] &&
				! Extensions::verify_extension_type(
					$this->_file['naive_ext'],
					MIMEs::TYPE_DEFAULT
				)
			)
		) {
			$this->_file['magic_type'] = '';
			return false;
		}

		// Keep the naive extension.
		if (
			$this->_file['naive_type'] === $this->_file['magic_type'] ||
			Extensions::verify_extension_type($this->_file['naive_ext'], $this->_file['magic_type'])
		) {
			$this->_file['magic_ext'] = $this->_file['naive_ext'];
			$this->_file['magic_group'] = $this->_file['naive_group'];

			// If the type is unchanged, the source is unchanged.
			if ($this->_file['naive_type'] === $this->_file['magic_type']) {
				$this->_file['magic_source'] = $this->_file['naive_source'];
			}
			// Otherwise it might be a little different.
			else {
				$this->_file['magic_source'] = Extensions::source(
					$this->_file['magic_ext'],
					$this->_file['magic_type']
				);
			}

			return true;
		}
		// Add our suggestions and bail.
		elseif (null !== ($exts = Types::extensions($this->_file['magic_type']))) {
			$this->_file['magic_ext'] = \array_key_first($exts);
			$this->_file['magic_source'] = $exts[$this->_file['magic_ext']]['source'];
			$this->_file['magic_group'] = Extensions::group($this->_file['magic_ext']);

			// Populate the names list.
			$this->_file['magic_suggested'] = array();
			foreach ($exts as $k=>$v) {
				$this->_file['magic_suggested'][$this->_file['filename'] . ".$k"] = $v['source'];
			}

			return true;
		}

		// We failed at magic. Reset all the fields just in case we blew
		// up late in the game.
		$this->_file['magic_ext'] = '';
		$this->_file['magic_type'] = '';
		$this->_file['magic_group'] = 0;
		$this->_file['magic_source'] = 0;
		$this->_file['magic_suggested'] = null;

		return false;
	}



	// -----------------------------------------------------------------
	// Getters
	// -----------------------------------------------------------------

	/**
	 * Basename
	 *
	 * @param bool $suggested Suggested (based on type rather than reality).
	 * @return ?string Name.
	 */
	public function basename(bool $suggested = false) : ?string {
		if (
			(null !== ($ext = $this->extension($suggested))) &&
			(null !== ($base = $this->filename()))
		) {
			return "$base.$ext";
		}

		return $this->_file['basename'] ?? null;
	}

	/**
	 * Parent Directory
	 *
	 * @return ?string Path.
	 */
	public function dirname() : ?string {
		return $this->_file['dirname'] ?? null;
	}

	/**
	 * Extension
	 *
	 * @param bool $suggested Suggested (based on type rather than reality).
	 * @return ?string Extension.
	 */
	public function extension(bool $suggested = false) : ?string {
		// We want the naive extension?
		if (! $suggested) {
			return $this->_file['naive_ext'] ?? null;
		}

		return $this->_file['extension'] ?? null;
	}

	/**
	 * File Name (Minus Extension)
	 *
	 * @return ?string Name.
	 */
	public function filename() : ?string {
		return $this->_file['filename'] ?? null;
	}

	/**
	 * Flags
	 *
	 * @return int Flags.
	 */
	public function flags() : int {
		return $this->_file['flags'] ?? 0;
	}

	/**
	 * Export Details
	 *
	 * This returns `pathinfo()` data for the file along with the type
	 * and whether or not that type matches.
	 *
	 * @return ?array Info.
	 */
	public function info() : ?array {
		$out = array(
			'dirname'=>$this->dirname() ?? '',
			'basename'=>$this->basename() ?? '',
			'filename'=>$this->filename() ?? '',
			'extension'=>$this->extension() ?? '',
			'type'=>$this->type() ?? '',
			'valid'=>$this->verify_extension_type(),
		);

		// Return it if we have anything.
		foreach ($out as $v) {
			if ($v) {
				return $out;
			}
		}

		return null;
	}

	/**
	 * Path
	 *
	 * Return the full file path.
	 *
	 * @return ?string Path.
	 */
	public function path() : ?string {
		return $this->_file['path'] ?? null;
	}

	/**
	 * Suggested Names
	 *
	 * If the magic type does not match the naive type, the file
	 * extension might need to be changed.
	 *
	 * @return ?array Suggested names.
	 */
	public function suggested() : ?array {
		// If we have a valid file, just return its basename.
		if ($this->verify_extension_type()) {
			return array($this->basename()=>$this->_file['naive_source']);
		}

		return $this->_file['magic_suggested'] ?? null;
	}

	/**
	 * Type
	 *
	 * @return ?string Type.
	 */
	public function type() : ?string {
		return $this->_file['type'] ?? null;
	}



	// -----------------------------------------------------------------
	// Evaluation
	// -----------------------------------------------------------------

	/**
	 * Is Empty?
	 *
	 * @return bool True/false.
	 */
	public function is_empty() : bool {
		return (bool) (
			! $this->exists() ||
			(MIMEs::FILE_EMPTY & $this->_file['flags'])
		);
	}

	/**
	 * File Exists?
	 *
	 * @return bool True/false.
	 */
	public function exists() : bool {
		return isset($this->_file['flags']) &&
			(MIMEs::FILE_EXISTS & $this->_file['flags']);
	}

	/**
	 * Is Readable?
	 *
	 * @return bool True/false.
	 */
	public function is_readable() : bool {
		return $this->exists() &&
			(MIMEs::FILE_READABLE & $this->_file['flags']);
	}

	/**
	 * Is Valid?
	 *
	 * Whether or not the file extension pairs with the file type.
	 *
	 * @return bool True/false.
	 */
	public function verify_extension_type() : bool {
		return isset($this->_file['flags']) &&
			(MIMEs::FILE_NAME_VALID & $this->_file['flags']);
	}



	// -----------------------------------------------------------------
	// Internal Helpers
	// -----------------------------------------------------------------

	/**
	 * Fileinfo Wrapper
	 *
	 * @return ?string Type.
	 */
	private function _fileinfo() : ?string {
		// If we're missing the primary dependency, let's not pretend
		// our guesses have any particular weight.
		if (! \function_exists('finfo_open') || ! \defined('FILEINFO_MIME_TYPE')) {
			return null;
		}

		// Try fileinfo.
		$finfo = \finfo_open(\FILEINFO_MIME_TYPE);
		if (
			(false !== ($magic = \finfo_file($finfo, $this->_file['path']))) &&
			(null !== ($magic = Sanitize::type(
				\strval($magic),
				MIMEs::FILTER_UPDATE_ALIAS
			)))
		) {
			// Some answers we just don't need.
			if (MIMEs::TYPE_EMPTY === $magic) {
				return null;
			}

			// Convert any generic MS Office encryption types to a more
			// testable (i.e. consistent) generic type.
			if (0 === \strpos($magic, 'application/cdfv2')) {
				$magic = MIMEs::TYPE_OFFICE;
			}

			// Let's see if we can do better.
			elseif (MIMEs::TYPE_DEFAULT === $magic) {
				if ($handle = \fopen($this->_file['path'], 'r')) {
					\fseek($handle, 0);
					$header = \fread($handle, 4);
					\fclose($handle);

					// Oddly, even as of PHP 7.3, `fileinfo.so` does not
					// always recognize magic WOFF headers.
					switch ($header) {
						case 'wOFF':
							return 'font/woff';
						case 'wOF2':
							return 'font/woff2';
					}
				}
			}
			// The .ogg extension can be used by audio files as well as
			// video files. You'd think Fileinfo would be able to figure
			// out which type it is, but the answer lies in ID3 tags
			// rather than magic headers.
			elseif (
				false !== \stripos($magic, '/ogg') &&
				(null !== ($id3 = $this->_fileinfo_ogg()))
			) {
				return $id3;
			}
			// A similar situation exists for .mp4 files, so if we can
			// parse ID3, let's do it!
			elseif (
				false !== \stripos($magic, '/mp4') &&
				(null !== ($id3 = $this->_fileinfo_mp4()))
			) {
				return $id3;
			}
		}

		return $magic ? $magic : null;
	}

	/**
	 * Fileinfo: In Context
	 *
	 * Reconcile magic determinations with expectations. This can help
	 * workaround various specific issues within the `fileinfo.so`
	 * library for various types of content.
	 *
	 * This will alter the magic type in place if it can or leave it
	 * as was.
	 *
	 * @todo Integrate Righteous SVG for .svg and .svgz workarounds.
	 *
	 * @param string $type Magic type.
	 * @return void Nothing.
	 */
	private function _fileinfo_context(string &$type) : void {
		// Don't waste time if we don't have a type.
		if (! $type) {
			return;
		}

		// What extension are we dealing with?
		$naive_ext = $this->_file['naive_ext'] ?? '';
		$naive_type = $this->_file['naive_type'] ?? '';

		// If we don't have an extension from the file itself, maybe
		// we can work backwards given the type of content. If not,
		// we're done here.
		if (! $naive_ext && (null === ($naive_ext = Types::extension($type)))) {
			return;
		}

		// Set the naive type too.
		if (! $naive_type && null !== ($tmp = Extensions::primary_type($naive_ext))) {
			$naive_type = $tmp;
		}

		// If expected and true types match — or are A/V aliases of one
		// another — we don't have to do anything else.
		if ($type === $naive_type || Types::is_av_alias($type, $naive_type)) {
			return;
		}
		// If the type is a more general alias of the extension we're
		// also done, but this time should prefer the naive type.
		elseif (Extensions::verify_extension_type($naive_ext, $type)) {
			$type = $naive_type;
			return;
		}

		// Fileinfo can have difficulty decoding SVG content if it is
		// missing the (technically required) XML headers. Eventually
		// we'll use Righteous SVG for this, but for now, we can simply
		// check to see the document has opening and closing SVG tags.
		if ('svg' === $naive_ext) {
			if ('image/svg+xml' !== $type) {
				$content = @\file_get_contents($this->_file['path']);
				if (
					(false !== $start = \stripos($content, '<svg')) &&
					false !== \stripos($content, '</svg>', $start)
				) {
					$type = 'image/svg+xml';
				}
			}

			return;
		}

		// Do we have a group?
		$naive_group = Extensions::group($naive_ext);

		// If we're expecting JSON data and didn't get it, let's just
		// try to decode it!
		if (MIMEs::GROUP_JSON & $naive_group) {
			$content = @\file_get_contents($this->_file['path']);
			if (null !== @\json_decode($content)) {
				if ($naive_type) {
					$type = $naive_type;
				}
			}

			return;
		}

		// There are about four dozen MS Office media types but over a
		// hundred different ways to store data in those formats. If
		// magic and naive disagree on the particulars but both point to
		// office, we should stick with the expected.
		if (
			(MIMEs::GROUP_OFFICE & $naive_group) &&
			Types::is_office_alias($type)
		) {
			if ($naive_type) {
				$type = $naive_type;
			}

			return;
		}

		// HTML and XML are easy to confuse because they are so similar
		// in construction, and really, so are all text files. In these
		// cases, it is better to trust the extension.
		if (
			(
				'text/html' === $type &&
				(MIMEs::GROUP_XML & $naive_group)
			) ||
			('application/xml' === $type && 'text/html' === $naive_type) ||
			(
				0 === \strpos($type, 'text/') &&
				(MIMEs::GROUP_TEXT & $naive_group)
			)
		) {
			$type = $naive_type;
			return;
		}
	}

	/**
	 * Fileinfo: MP4
	 *
	 * While there are dedicated .m4a and .m4v extensions, both audio
	 * and video formats frequently share a .mp4 extension. For some
	 * reason `fileinfo.so` can't tell the difference.
	 *
	 * We can make a reasonable guess ourselves by parsing the ID3 tags.
	 *
	 * @return ?string Type.
	 */
	private function _fileinfo_mp4() : ?string {
		if (null !== $id3 = $this->_id3_info()) {
			// This could be audio if the signature is M4A.
			if (
				isset($id3['quicktime']['ftype']['signature']) &&
				'M4A' === $id3['quicktime']['ftype']['signature']
			) {
				return 'audio/mp4';
			}
			// This could also be audio if there is no video resolution.
			elseif (
				! isset($id3['video']['resolution_x'], $id3['video']['resolution_y']) ||
				! $id3['video']['resolution_x'] ||
				! $id3['video']['resolution_y']
			) {
				return 'audio/mp4';
			}
			// Trust the MIME type.
			elseif (
				isset($id3['mime_type']) &&
				(
					'audio/mp4' === $id3['mime_type'] ||
					'video/mp4' === $id3['mime_type']
				)
			) {
				return $id3['mime_type'];
			}
		}

		return null;
	}

	/**
	 * Fileinfo: Ogg
	 *
	 * While there are dedicated .oga and .ogv extensions, both audio
	 * and video formats frequently share a .ogg extension. For some
	 * reason `fileinfo.so` can't tell the difference.
	 *
	 * We can make a reasonable guess ourselves by parsing the ID3 tags.
	 *
	 * @return ?string Type.
	 */
	private function _fileinfo_ogg() : ?string {
		if (null !== $id3 = $this->_id3_info()) {
			// It is a video if it has dimensions.
			if (
				isset($id3['video']['resolution_x'], $id3['video']['resolution_y']) &&
				0 < $id3['video']['resolution_x'] &&
				0 < $id3['video']['resolution_y']
			) {
				return 'video/ogg';
			}
			// If it is Ogg-like, trust the ID3 MIME.
			elseif (
				isset($id3['mime_type']) &&
				\preg_match('#^(audio|video)/ogg#', $id3['mime_type'], $match) &&
				isset($match[1]) &&
				$match[1]
			) {
				return $match[1] . '/ogg';
			}
		}

		return null;
	}

	/**
	 * ID3 Tags
	 *
	 * Some content-based identification can be found hidden in the
	 * media's ID3 tags.
	 *
	 * Righteous MIMEs does not (yet) implement ID3 parsing directly,
	 * but if the `getID3` library is present, we'll use it!
	 *
	 * @return ?array MIME.
	 */
	private function _id3_info() : ?array {
		if (! \class_exists('getID3')) {
			return null;
		}

		$id3 = new getID3();
		$out = $id3->analyze($this->_file['path']);
		return ! empty($out) && \is_array($out) ? $out : null;
	}
}

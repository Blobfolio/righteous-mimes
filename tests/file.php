<?php
/**
 * Unit Tests: File
 *
 * These tests cover all of the public methods in the class, hopefully
 * prodding all of the edge cases too.
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

use Righteous\MIMEs\File;



if (file_exists('/usr/share/php/getid3/autoload.php')) {
	require_once '/usr/share/php/getid3/autoload.php';
}



class file_tests extends \PHPUnit\Framework\TestCase {
	const ASSET_DIR = __DIR__ . '/assets';

	const FILES = array(
		// -------------------------------------------------------------
		// Audio
		// -------------------------------------------------------------

		array(
			'file'=>file_tests::ASSET_DIR . '/audio.flac',
			'ext'=>'flac',
			'type'=>'audio/flac',
			'naked_ext'=>'flac',
			'naked_type'=>'audio/flac',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/audio.mp3',
			'ext'=>'mp3',
			'type'=>'audio/mpeg',
			'naked_ext'=>'mp3',
			'naked_type'=>'audio/mpeg',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/audio.mp4',
			'ext'=>'mp4',
			'type'=>'audio/mp4',
			'naked_ext'=>'m4a',
			'naked_type'=>'audio/mp4',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/audio.ogg',
			'ext'=>'ogg',
			'type'=>'audio/ogg',
			'naked_ext'=>'oga',
			'naked_type'=>'audio/ogg',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/audio.wav',
			'ext'=>'wav',
			'type'=>'audio/wav',
			'naked_ext'=>'wav',
			'naked_type'=>'audio/wav',
		),

		// -------------------------------------------------------------
		// MS Excel
		// -------------------------------------------------------------

		// Using an encrypted format.
		array(
			'file'=>file_tests::ASSET_DIR . '/xls-cdfv2.xls',
			'ext'=>'xls',
			'type'=>'application/vnd.ms-excel',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		// Using the "Excel" format.
		array(
			'file'=>file_tests::ASSET_DIR . '/xls-excel.xls',
			'ext'=>'xls',
			'type'=>'application/vnd.ms-excel',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		// Using the "MS Office" format.
		array(
			'file'=>file_tests::ASSET_DIR . '/xls-msoffice.xls',
			'ext'=>'xls',
			'type'=>'application/vnd.ms-excel',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		// Using the XML format.
		array(
			'file'=>file_tests::ASSET_DIR . '/xls-xml.xls',
			'ext'=>'xls',
			'type'=>'application/vnd.ms-excel',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		// -------------------------------------------------------------
		// Fonts
		// -------------------------------------------------------------

		array(
			'file'=>file_tests::ASSET_DIR . '/font.eot',
			'ext'=>'eot',
			'type'=>'application/vnd.ms-fontobject',
			'naked_ext'=>'eot',
			'naked_type'=>'application/vnd.ms-fontobject',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/font.otf',
			'ext'=>'otf',
			'type'=>'font/otf',
			'naked_ext'=>'otf',
			'naked_type'=>'font/otf',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/font.ttf',
			'ext'=>'ttf',
			'type'=>'font/ttf',
			'naked_ext'=>'ttf',
			'naked_type'=>'font/ttf',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/font.woff',
			'ext'=>'woff',
			'type'=>'font/woff',
			'naked_ext'=>'woff',
			'naked_type'=>'font/woff',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/font.woff2',
			'ext'=>'woff2',
			'type'=>'font/woff2',
			'naked_ext'=>'woff2',
			'naked_type'=>'font/woff2',
		),

		// -------------------------------------------------------------
		// Images
		// -------------------------------------------------------------

		// An Illustrator file with "Create PDF Compatible File" set.
		array(
			'file'=>file_tests::ASSET_DIR . '/image.ai',
			'ext'=>'ai',
			'type'=>'application/postscript',
			'naked_ext'=>'pdf',
			'naked_type'=>'application/pdf',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.bmp',
			'ext'=>'bmp',
			'type'=>'image/bmp',
			'naked_ext'=>'bmp',
			'naked_type'=>'image/bmp',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.eps',
			'ext'=>'eps',
			'type'=>'application/postscript',
			'naked_ext'=>'eps',
			'naked_type'=>'application/postscript',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.gif',
			'ext'=>'gif',
			'type'=>'image/gif',
			'naked_ext'=>'gif',
			'naked_type'=>'image/gif',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.heic',
			'ext'=>'heic',
			'type'=>'image/heic',
			'naked_ext'=>'heic',
			'naked_type'=>'image/heic',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.ico',
			'ext'=>'ico',
			'type'=>'image/vnd.microsoft.icon',
			'naked_ext'=>'ico',
			'naked_type'=>'image/vnd.microsoft.icon',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.jpg',
			'ext'=>'jpg',
			'type'=>'image/jpeg',
			'naked_ext'=>'jpg',
			'naked_type'=>'image/jpeg',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.png',
			'ext'=>'png',
			'type'=>'image/png',
			'naked_ext'=>'png',
			'naked_type'=>'image/png',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.ps',
			'ext'=>'ps',
			'type'=>'application/postscript',
			'naked_ext'=>'eps',
			'naked_type'=>'application/postscript',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.psd',
			'ext'=>'psd',
			'type'=>'image/vnd.adobe.photoshop',
			'naked_ext'=>'psd',
			'naked_type'=>'image/vnd.adobe.photoshop',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.svg',
			'ext'=>'svg',
			'naked_ext'=>null,
			'type'=>'image/svg+xml',
			'naked_type'=>null,
		),

		// This SVG code is missing the expected XML definitions, but is
		// otherwise valid.
		array(
			'file'=>file_tests::ASSET_DIR . '/image-notype.svg',
			'ext'=>'svg',
			'naked_ext'=>null,
			'type'=>'image/svg+xml',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.svgz',
			'ext'=>'svgz',
			'naked_ext'=>null,
			'type'=>'image/svg+xml',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.tif',
			'ext'=>'tif',
			'type'=>'image/tiff',
			'naked_ext'=>'tif',
			'naked_type'=>'image/tiff',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image-jpeg.tif',
			'ext'=>'tif',
			'type'=>'image/tiff',
			'naked_ext'=>'tif',
			'naked_type'=>'image/tiff',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image-lzw.tif',
			'ext'=>'tif',
			'type'=>'image/tiff',
			'naked_ext'=>'tif',
			'naked_type'=>'image/tiff',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image-packbits.tif',
			'ext'=>'tif',
			'type'=>'image/tiff',
			'naked_ext'=>'tif',
			'naked_type'=>'image/tiff',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image-zip.tif',
			'ext'=>'tif',
			'type'=>'image/tiff',
			'naked_ext'=>'tif',
			'naked_type'=>'image/tiff',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.webp',
			'ext'=>'webp',
			'type'=>'image/webp',
			'naked_ext'=>'webp',
			'naked_type'=>'image/webp',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/image.xcf',
			'ext'=>'xcf',
			'type'=>'image/x-xcf',
			'naked_ext'=>'xcf',
			'naked_type'=>'image/x-xcf',
		),

		// -------------------------------------------------------------
		// MusicXML
		// -------------------------------------------------------------

		// Standard XML format.
		array(
			'file'=>file_tests::ASSET_DIR . '/beet.musicxml',
			'ext'=>'musicxml',
			'naked_ext'=>null,
			'type'=>'application/vnd.recordare.musicxml+xml',
			'naked_type'=>null,
		),

		// The binary format.
		array(
			'file'=>file_tests::ASSET_DIR . '/beet.mxl',
			'ext'=>'mxl',
			'naked_ext'=>null,
			'type'=>'application/vnd.recordare.musicxml',
			'naked_type'=>null,
		),

		// -------------------------------------------------------------
		// PowerPoint
		// -------------------------------------------------------------

		array(
			'file'=>file_tests::ASSET_DIR . '/pp.ppsx',
			'ext'=>'ppsx',
			'naked_ext'=>null,
			'type'=>'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/pp.pptx',
			'ext'=>'pptx',
			'naked_ext'=>null,
			'type'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'naked_type'=>null,
		),

		// -------------------------------------------------------------
		// Text/Documents
		// -------------------------------------------------------------

		array(
			'file'=>file_tests::ASSET_DIR . '/text.azw3',
			'ext'=>'azw3',
			'naked_ext'=>null,
			'type'=>'application/vnd.amazon.mobi8-ebook',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.brf',
			'ext'=>'brf',
			'naked_ext'=>null,
			'type'=>'text/plain',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.csv',
			'ext'=>'csv',
			'naked_ext'=>null,
			'type'=>'text/csv',
			'naked_type'=>null,
		),

		// This is an empty document with a CSV extension.
		array(
			'file'=>file_tests::ASSET_DIR . '/text-empty.csv',
			'ext'=>'csv',
			'naked_ext'=>null,
			'type'=>'text/csv',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.gpx',
			'ext'=>'gpx',
			'type'=>'application/gpx+xml',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.epub',
			'ext'=>'epub',
			'naked_ext'=>null,
			'type'=>'application/epub+zip',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.mobi',
			'ext'=>'mobi',
			'naked_ext'=>null,
			'type'=>'application/x-mobipocket-ebook',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.mscx',
			'ext'=>'mscx',
			'type'=>'application/x-musescore+xml',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.mscz',
			'ext'=>'mscz',
			'type'=>'application/x-musescore',
			'naked_ext'=>null,
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.odt',
			'ext'=>'odt',
			'naked_ext'=>null,
			'type'=>'application/vnd.oasis.opendocument.text',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.rtf',
			'ext'=>'rtf',
			'type'=>'application/rtf',
			'naked_ext'=>'rtf',
			'naked_type'=>'application/rtf',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.sat',
			'ext'=>'sat',
			'naked_ext'=>null,
			'type'=>'text/plain',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/text.txt',
			'ext'=>'txt',
			'naked_ext'=>null,
			'type'=>'text/plain',
			'naked_type'=>null,
		),

		// A PDF generated by GIMP (consisting of just an image).
		array(
			'file'=>file_tests::ASSET_DIR . '/text-gimp.pdf',
			'ext'=>'pdf',
			'type'=>'application/pdf',
			'naked_ext'=>'pdf',
			'naked_type'=>'application/pdf',
		),

		// A PDF generated by LibreOffice (consisting of text).
		array(
			'file'=>file_tests::ASSET_DIR . '/text-libre.pdf',
			'ext'=>'pdf',
			'type'=>'application/pdf',
			'naked_ext'=>'pdf',
			'naked_type'=>'application/pdf',
		),

		// -------------------------------------------------------------
		// Video
		// -------------------------------------------------------------

		array(
			'file'=>file_tests::ASSET_DIR . '/video.avi',
			'ext'=>'avi',
			'naked_ext'=>null,
			'type'=>'video/x-msvideo',
			'naked_type'=>null,
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video.mkv',
			'ext'=>'mkv',
			'type'=>'video/x-matroska',
			'naked_ext'=>'mkv',
			'naked_type'=>'video/x-matroska',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video.mov',
			'ext'=>'mov',
			'type'=>'video/quicktime',
			'naked_ext'=>'mov',
			'naked_type'=>'video/quicktime',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video.mp4',
			'ext'=>'mp4',
			'type'=>'video/mp4',
			'naked_ext'=>'m4v',
			'naked_type'=>'video/mp4',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video-av1.mp4',
			'ext'=>'mp4',
			'type'=>'video/mp4',
			'naked_ext'=>'m4v',
			'naked_type'=>'video/mp4',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video.ogg',
			'ext'=>'ogg',
			'type'=>'video/ogg',
			'naked_ext'=>'ogv',
			'naked_type'=>'video/ogg',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video.mpg',
			'ext'=>'mpg',
			'type'=>'video/mpeg',
			'naked_ext'=>'mpg',
			'naked_type'=>'video/mpeg',
		),

		array(
			'file'=>file_tests::ASSET_DIR . '/video.webm',
			'ext'=>'webm',
			'type'=>'video/webm',
			'naked_ext'=>'webm',
			'naked_type'=>'video/webm',
		),
	);



	// -----------------------------------------------------------------
	// Tests
	// -----------------------------------------------------------------

	/**
	 * Test File
	 *
	 * @dataProvider _file
	 * @param array $args Arguments.
	 * @param ?array $expected Expected.
	 * @param ?string $naked_ext Extension found from extensionless file name.
	 * @param ?string $naked_type Type found from extensionless file name.
	 */
	function test_file(
		array $args,
		?array $expected,
		?string $naked_ext = null,
		?string $naked_type = null
	) : void {
		$file = new File(...$args);
		$this->assertSame($expected, $file->info());

		// Try it without an extension.
		if (null !== $naked_ext || null !== $naked_type) {
			// Copy the file to a location without an extension.
			$tmp = '/tmp/naked_test';
			\copy($args[0], $tmp);
			$this->assertTrue(\is_file($tmp));

			// Pull file info from this copy.
			$file = new File($tmp);
			\unlink($tmp);

			// For testing purposes, null means "don't test".
			if (null !== $naked_ext) {
				$this->assertSame($naked_ext, $file->extension(true));
			}
			if (null !== $naked_type) {
				$this->assertSame($naked_type, $file->type());
			}
		}
	}

	/**
	 * Test File (Wrong Name)
	 */
	function test_file_wrong() : void {
		$real = self::ASSET_DIR . '/audio.flac';
		$fake = '/tmp/fake.mp3';

		// Make a copy.
		$this->assertTrue(\is_file($real));
		\copy($real, $fake);
		$this->assertTrue(\is_file($fake));

		$real_file = new File($real);
		$fake_file = new File($fake);

		// Cleanup.
		\unlink($fake);

		// The types should match.
		$this->assertSame($real_file->type(), $fake_file->type());

		// Test extension-fetching.
		$this->assertSame('flac', $real_file->extension());
		$this->assertSame('flac', $real_file->extension(false));
		$this->assertSame('flac', $real_file->extension(true));

		$this->assertSame('mp3', $fake_file->extension());
		$this->assertSame('mp3', $fake_file->extension(false));
		$this->assertSame('flac', $fake_file->extension(true));

		// Test basename.
		$this->assertSame('audio.flac', $real_file->basename());
		$this->assertSame('audio.flac', $real_file->basename(false));
		$this->assertSame('audio.flac', $real_file->basename(true));

		$this->assertSame('fake.mp3', $fake_file->basename());
		$this->assertSame('fake.mp3', $fake_file->basename(false));
		$this->assertSame('fake.flac', $fake_file->basename(true));

		// Real should suggest itself, while fake should suggest a new
		// name.
		$suggestion = $real_file->suggested();
		$this->assertTrue(\is_array($suggestion));
		$this->assertSame(
			array('audio.flac'),
			\array_keys($suggestion)
		);

		$suggestion = $fake_file->suggested();
		$this->assertTrue(\is_array($suggestion));
		$this->assertSame(
			array('fake.flac'),
			\array_keys($suggestion)
		);

		// Test the tests.
		$this->assertFalse($real_file->is_empty());
		$this->assertTrue($real_file->exists());
		$this->assertTrue($real_file->is_readable());
		$this->assertTrue($real_file->verify_extension_type());

		$this->assertFalse($fake_file->is_empty());
		$this->assertFalse($fake_file->verify_extension_type());
		$this->assertTrue($fake_file->exists());
		$this->assertTrue($fake_file->is_readable());
	}



	// -----------------------------------------------------------------
	// Data
	// -----------------------------------------------------------------

	/**
	 * Data: File
	 *
	 * @return array Data.
	 */
	function _file() : array {
		$out = array();

		// Let's start with the files we have.
		foreach (self::FILES as $v) {
			$out[] = array(
				array($v['file']),
				array(
					'dirname'=>\dirname($v['file']),
					'basename'=>\basename($v['file']),
					'filename'=>\pathinfo($v['file'], \PATHINFO_FILENAME),
					'extension'=>\pathinfo($v['file'], \PATHINFO_EXTENSION),
					'type'=>$v['type'],
					'valid'=>true,
				),
				$v['naked_ext'],
				$v['naked_type'],
			);
		}

		// Let's test a URL to make sure that comes out right.
		$out[] = array(
			array('https://wikitech.wikimedia.org/static/images/project-logos/wikitech.png'),
			array(
				'dirname'=>'/static/images/project-logos',
				'basename'=>'wikitech.png',
				'filename'=>'wikitech',
				'extension'=>'png',
				'type'=>'image/png',
				'valid'=>true,
			),
			null,
			null,
		);

		return $out;
	}
}

<?php
/**
 * Unit Tests: Sanitize
 *
 * These tests cover all of the public methods in the class, hopefully
 * prodding all of the edge cases too.
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

use Righteous\MIMEs;
use Righteous\MIMEs\Sanitize;



if (file_exists('/usr/share/php/getid3/autoload.php')) {
	require_once '/usr/share/php/getid3/autoload.php';
}



class sanitize_tests extends \PHPUnit\Framework\TestCase {
	// -----------------------------------------------------------------
	// Tests
	// -----------------------------------------------------------------

	/**
	 * Sanitize Extension
	 *
	 * @dataProvider _extension
	 * @param array $args Args.
	 * @param ?string $expected Expected.
	 */
	function test_extension(array $args, ?string $expected) : void {
		// Test the original file.
		$result = Sanitize::extension(...$args);
		$this->assertSame($expected, $result);
	}

	/**
	 * Sanitize Type
	 *
	 * @dataProvider _type
	 * @param array $args Args.
	 * @param ?string $expected Expected.
	 */
	function test_type(array $args, ?string $expected) : void {
		// Test the original file.
		$result = Sanitize::type(...$args);
		$this->assertSame($expected, $result);
	}



	// -----------------------------------------------------------------
	// Data
	// -----------------------------------------------------------------

	/**
	 * Data: Extension
	 *
	 * @return array Data.
	 */
	function _extension() : array {
		return array(
			// An extension by itself.
			array(
				array('gz'),
				'gz',
			),
			// An extension with a leading dot still attached.
			array(
				array('.JPG'),
				'jpg',
			),
			// A file name, wrong case.
			array(
				array('index.HTML'),
				'html',
			),
			// Some crap at the end.
			array(
				array('script.sh~'),
				'sh',
			),
			// Double extension.
			array(
				array('file.tar.xz'),
				'xz',
			),
			// A file path.
			array(
				array(__DIR__ . '/assets/image.svg'),
				'svg',
			),
			// A URL.
			array(
				array('https://wikitech.wikimedia.org/static/images/project-logos/wikitech.png'),
				'png',
			),
			// A directory.
			array(
				array(__DIR__),
				null,
			),
			// Test NO_UNKNOWN.
			array(
				array('file.sarcosuchus'),
				'sarcosuchus',
			),
			array(
				array(
					'file.sarcosuchus',
					MIMEs::FILTER_NO_UNKNOWN,
				),
				null,
			),
		);
	}

	/**
	 * Data: Type
	 *
	 * @return array Data.
	 */
	function _type() : array {
		return array(
			// Basic checks.
			array(
				array('AWES0ME--file/~.-type-.~'),
				'awes0me-file/type',
			),
			array(
				array('apples'),
				null,
			),
			array(
				array('+fruit+/apples++xml'),
				'fruit/apples+xml',
			),
			// Test alias flags.
			array(
				array('image/x-ms-bmp'),
				'image/x-ms-bmp',
			),
			array(
				array(
					'image/x-ms-bmp',
					MIMEs::FILTER_NO_ALIAS,
				),
				null,
			),
			array(
				array(
					'image/x-ms-bmp',
					MIMEs::FILTER_UPDATE_ALIAS,
				),
				'image/bmp',
			),
			array(
				array(
					'image/vnd.bmp',
					MIMEs::FILTER_UPDATE_ALIAS,
				),
				'image/bmp',
			),
			array(
				array(
					'image/x-ms-bmp',
					MIMEs::FILTER_NO_ALIAS | MIMEs::FILTER_UPDATE_ALIAS,
				),
				'image/bmp',
			),
			// Test default flags.
			array(
				array(MIMEs::TYPE_DEFAULT),
				MIMEs::TYPE_DEFAULT,
			),
			array(
				array(
					MIMEs::TYPE_DEFAULT,
					MIMEs::FILTER_NO_DEFAULT,
				),
				null,
			),
			// Test empty flags.
			array(
				array(MIMEs::TYPE_EMPTY),
				MIMEs::TYPE_EMPTY,
			),
			array(
				array(
					MIMEs::TYPE_EMPTY,
					MIMEs::FILTER_NO_EMPTY,
				),
				null,
			),
			// Test unknown flags.
			array(
				array('quetzalcoatlus/northropi'),
				'quetzalcoatlus/northropi',
			),
			array(
				array(
					'quetzalcoatlus/northropi',
					MIMEs::FILTER_NO_UNKNOWN,
				),
				null,
			),
		);
	}
}

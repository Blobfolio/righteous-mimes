<?php
/**
 * Righteous MIMES: Data Crunching
 *
 * This script will process raw data — obtained earlier in the build
 * process — and generate the library's JSON and PHP data feeds.
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build;

use Righteous\MIMEs;
use function Righteous\Build\collect_data;
use function Righteous\Build\json_to_php;
use const Righteous\Build\BIN_DIR;
use const Righteous\Build\LIB_DIR;
use const Righteous\Build\OUT_DIR;
use const Righteous\Build\ROOT_DIR;
use const Righteous\Build\SKEL_DIR;
use const Righteous\Build\TMP_DIR;



// Some convenience constants for ourselves.
\define('Righteous\\Build\\ROOT_DIR', \realpath(\dirname(\dirname(__DIR__))));
\define('Righteous\\Build\\BIN_DIR', ROOT_DIR . '/bin');
\define('Righteous\\Build\\LIB_DIR', ROOT_DIR . '/lib');
\define('Righteous\\Build\\OUT_DIR', LIB_DIR . '/righteous/mimes/data');
\define('Righteous\\Build\\SKEL_DIR', ROOT_DIR . '/skel');
\define('Righteous\\Build\\TMP_DIR', '/tmp/raw-mimes');

// Some quick error checking.
if (
	! \is_dir(BIN_DIR) ||
	! \is_dir(LIB_DIR) ||
	! \is_dir(OUT_DIR) ||
	! \is_dir(SKEL_DIR) ||
	! \is_dir(TMP_DIR)
) {
	exit(1);
}

// We should steal some functionality from the main library.
require LIB_DIR . '/vendor/autoload.php';

// This is ours.
require __DIR__ . '/source.php';
require __DIR__ . '/source/apache.php';
require __DIR__ . '/source/blobfolio.php';
require __DIR__ . '/source/freedesktop.php';
require __DIR__ . '/source/iana.php';
require __DIR__ . '/source/nginx.php';
require __DIR__ . '/source/tika.php';

// Early parsed data.
$data = array();

/**
 * Collate Data
 *
 * @param string $class Class.
 * @param string $path Data path.
 * @return void Nothing.
 */
function collect_data(string $class, string $path) : void {
	global $data;

	if (null === ($result = $class::parse($path))) {
		exit(1);
	}

	$data[$class::FLAG] = $result;
}

/**
 * JSON to PHP
 *
 * @param string $json JSON.
 * @param bool $strip_num_keys Strip quoted numeric keys.
 * @return ?string PHP code.
 */
function json_to_php(string $json, bool $strip_num_keys = false) : ?string {
	if (null === ($json = \json_decode($json, true))) {
		return null;
	}

	// Var export does most of the work.
	$out = \var_export($json, true);

	// But their styles don't align with our own.
	$out = \preg_replace('/\s*=>\s*/', '=>', $out);
	$out = \str_replace('array (', 'array(', $out);
	$out = \str_replace('  ', "\t", $out);
	$out = \preg_replace("/^\t(\d+)=>/m", "\t'$1'=>", $out);
	$out = \preg_replace('/\d+=>/', '', $out);

	// We need to add tabs to make it line up.
	$out = \explode("\n", $out);
	foreach ($out as $k=>$v) {
		if ($k) {
			$out[$k] = "\t" . $v;
		}
	}

	$out = \implode("\n", $out);

	if ($strip_num_keys) {
		$out = \preg_replace("/'(\d+)'=>/", '', $out);
	}

	return $out;
}

// Collect the data.
collect_data(
	'Righteous\\Build\\Source\\Apache',
	TMP_DIR . '/apache.txt'
);
collect_data(
	'Righteous\\Build\\Source\\Blobfolio',
	SKEL_DIR . '/blobfolio.csv'
);
collect_data(
	'Righteous\\Build\\Source\\FreeDesktop',
	TMP_DIR . '/freedesktop.xml'
);
collect_data(
	'Righteous\\Build\\Source\\Iana',
	TMP_DIR . '/iana/media-types'
);
collect_data(
	'Righteous\\Build\\Source\\Nginx',
	TMP_DIR . '/nginx.txt'
);
collect_data(
	'Righteous\\Build\\Source\\Tika',
	TMP_DIR . '/tika.xml'
);

// Now that we have all the data separated by source, let's merge them.
$out_aliases = array();
$out_derived = array(
	'gzip'=>array(),
	'json'=>array(),
	'office'=>array(),
	'text'=>array(),
	'xml'=>array(),
	'zip'=>array(),
);
$out_extensions = array();
$out_office = array();
$out_primaries = array();
$out_types = array();

// We have a few extra aliases to throw into the mix that are bound to
// specific type patterns. New types might spring up, so that's why
// we're applying them now.
$type_aliases = array(
	'application/vnd.ms-word'=>array(
		MIMEs::TYPE_OFFICE,
		'application/xml',
	),
	'application/vnd.ms-excel'=>array(
		MIMEs::TYPE_OFFICE,
		'application/xml',
	),
	'application/vnd.ms-powerpoint'=>array(
		MIMEs::TYPE_OFFICE,
	),
	'application/vnd.openxmlformats-officedocument'=>array(
		MIMEs::TYPE_OFFICE,
	),
	'application/vnd.ms-excel.sheet.macroenabled.12'=>array(
		'application/zip',
	),
);

// Pull together an extensions list first.
foreach ($data as $source=>$exts) {
	foreach ($exts as $ext=>$types) {
		if (! isset($out_extensions[$ext])) {
			$out_extensions[$ext] = array();
		}

		// Clean up the flags.
		foreach ($types as $type=>$flags) {
			if (! isset($out_extensions[$ext][$type])) {
				$out_extensions[$ext][$type] = $flags;
			}
			else {
				$out_extensions[$ext][$type] |= $flags;
			}
		}

		// One more pass to add extra aliases, if any.
		foreach ($out_extensions[$ext] as $type=>$flags) {
			foreach ($type_aliases as $k=>$v) {
				if (0 === \strpos($type, $k)) {
					foreach ($v as $v2) {
						if (isset($out_extensions[$ext][$v2])) {
							$out_extensions[$ext][$v2] |= (MIMEs::SOURCE_ALIAS | MIMEs::SOURCE_BLOBFOLIO);
						}
						else {
							$out_extensions[$ext][$v2] = MIMEs::SOURCE_ALIAS | MIMEs::SOURCE_BLOBFOLIO;
						}
					}
				}
			}
		}
	}
}

// We have a few manual associations to associate.
$manual_primaries = \file_get_contents(SKEL_DIR . '/manual-types.json');
if (null === ($manual_primaries = \json_decode($manual_primaries, true))) {
	exit(1);
}
foreach ($manual_primaries as $ext=>$type) {
	$out_extensions[$ext][$type] = MIMEs::SOURCE_CERTAIN;
}

// We should also manually remove some invalid entries if present.
if (isset($out_extensions['svgz']['application/xml'])) {
	unset($out_extensions['svgz']['application/xml']);
}

// Reformat the data to make it more easily sortable.
\ksort($out_extensions);
foreach ($out_extensions as $k=>$v) {
	$certain = (bool) (1 === \count($v));

	foreach ($v as $k2=>$v2) {
		// A perfect score comes with lack of choice or manual override.
		if (
			$certain ||
			(isset($manual_primaries[$k]) && $manual_primaries[$k] === $k2)
		) {
			$authority = 999;

			// Treat it like ours since it kinda is.
			if (! $certain) {
				$v2 |= MIMEs::SOURCE_BLOBFOLIO;
			}
		}
		// Otherwise we calculate it from the source(s) present.
		else {
			$authority = MIMEs::source_authority($v2);
		}

		$out_extensions[$k][$k2] = array(
			'authority'=>$authority,
			'source'=>$v2,
		);
	}

	// Sort by authority.
	\uasort($out_extensions[$k], function($a, $b) {
		return $b['authority'] <=> $a['authority'];
	});
}

// Note primary types and assign groups.
$primaries = array();
foreach ($out_extensions as $ext=>$types) {
	$type = \array_key_first($types);
	if (! isset($primaries[$type])) {
		$primaries[$type] = 0;
	}
	++$primaries[$type];
}

// Second pass, map all the aliases to it.
foreach ($out_extensions as $ext=>$types) {
	$primary = \array_key_first($types);
	$primary_authority = $types[$primary]['authority'];

	foreach ($types as $type=>$info) {
		// Don't do anything with primaries in secondary positions.
		if ($type !== $primary && isset($primaries[$type])) {
			continue;
		}

		if ($type === $primary) {
			$authority = 999;
		}
		elseif (isset($primaries[$type])) {
			continue;
		}
		else {
			$authority = $primary_authority;
		}

		if (! isset($out_aliases[$type])) {
			$out_aliases[$type] = array();
		}

		if (! isset($out_aliases[$type][$primary])) {
			$out_aliases[$type][$primary] = $authority;
		}
		elseif ($authority > $out_aliases[$type][$primary]) {
			$out_aliases[$type][$primary] = $authority;
		}
	}
}

// Start our primary list.
$out_primaries = \array_values($manual_primaries);

// Loop the aliases again, getting rid of anything with more than one
// value; we just can't trust that. We can also add the primaries to
// our primary list.
\ksort($out_aliases);
foreach ($out_aliases as $k=>$v) {
	if (1 === \count($out_aliases[$k])) {
		$key = \array_key_first($v);
		$out_aliases[$k] = $key;
		$out_primaries[] = $key;
	}
	else {
		$out_primaries = \array_merge($out_primaries, \array_keys($out_aliases[$k]));
		unset($out_aliases[$k]);
	}
}

// Clean up primaries.
$out_primaries = \array_unique($out_primaries);
\sort($out_primaries);

// Last thing, make sure all primaries point to themselves in the alias
// list.
foreach ($out_primaries as $p) {
	$out_aliases[$p] = $p;
}
\ksort($out_aliases);

// Now we can loop the main data again to build: A) a reverse list of
// types to extension; B) a derived list of Zip (etc)-like extensions;
foreach ($out_extensions as $ext=>$types) {
	foreach ($types as $type=>$info) {
		// Let's start with the reverse list.
		if (! isset($out_types[$type])) {
			$out_types[$type] = array();
		}

		if (! isset($out_types[$type][$ext])) {
			$out_types[$type][$ext] = $info;
			$out_types[$type][$ext]['extension'] = $ext;
		}
		else {
			$out_types[$type][$ext]['source'] |= $info['source'];
		}

		// Now checkout derived fits.
		if ('application/gzip' === $type) {
			$out_derived['gzip'][] = $ext;
		}
		if ('application/json' === $type || '+json' === \substr($type, -5)) {
			$out_derived['json'][] = $ext;
		}
		if (
			'xml' !== $ext &&
			(MIMEs::TYPE_OFFICE === $type)
		) {
			$out_derived['office'][] = $ext;
		}
		if (0 === \strpos($type, 'text/')) {
			$out_derived['text'][] = $ext;
		}
		if (
			'svgz' !== $ext &&
			('application/xml' === $type || '+xml' === \substr($type, -4))
		) {
			$out_derived['xml'][] = $ext;
		}
		if ('application/zip' === $type) {
			$out_derived['zip'][] = $ext;
		}
	}
}

// Loop again to clean and sort the type list.
\ksort($out_types);
foreach ($out_types as $type=>$exts) {
	$certain = (bool) (1 === \count($exts));
	foreach ($exts as $ext=>$info) {
		if (
			$certain ||
			(isset($manual_primaries[$ext]) && $manual_primaries[$ext] === $type)
		) {
			$out_types[$type][$ext]['authority'] = 999;
		}
		else {
			$out_types[$type][$ext]['authority'] = MIMEs::source_authority($info['source']);
		}
	}

	// Sort by authority.
	\uasort($out_types[$type], function($a, $b) {
		// If the scores match, prefer extensions with three characters.
		if ($a['authority'] === $b['authority']) {
			$a_len = \strlen($a['extension']) === 3 ? 0 : 1;
			$b_len = \strlen($b['extension']) === 3 ? 0 : 1;

			return $a_len <=> $b_len;
		}

		return $b['authority'] <=> $a['authority'];
	});

	// We can remove the redundant extension key now.
	foreach ($out_types[$type] as $ext=>$info) {
		unset($out_types[$type][$ext]['extension']);
	}
}

// Clean up the derived associations.
foreach ($out_derived as $k=>$v) {
	$out_derived[$k] = \array_unique($v);
	\sort($out_derived[$k]);
}

// Build a reverse type list for office aliases.
foreach ($out_derived['office'] as $ext) {
	$out_office = \array_merge(
		$out_office,
		\array_keys($out_extensions[$ext])
	);
}
$out_office = \array_unique($out_office);
$out_office = \array_diff(
	$out_office,
	array(
		'application/xml',
		'application/zip',
		'text/plain',
	)
);
\sort($out_office);

// Real quick, before saving our extension and type data, let's
// normalize the authority values.
$certainty = MIMEs::source_authority(MIMEs::SOURCE_CERTAIN);
foreach ($out_extensions as $ext=>$types) {
	foreach ($types as $type=>$v) {
		if ($v['authority'] > $certainty) {
			$out_extensions[$ext][$type]['authority'] = $certainty;
		}
	}
}
foreach ($out_types as $type=>$exts) {
	foreach ($exts as $ext=>$v) {
		if ($v['authority'] > $certainty) {
			$out_types[$type][$ext]['authority'] = $certainty;
		}
	}
}

// Finally! Let's save what we've got!
$out_aliases = \json_encode($out_aliases);
\file_put_contents(BIN_DIR . '/aliases.json', $out_aliases);

$out_extensions = \json_encode($out_extensions);
\file_put_contents(BIN_DIR . '/extensions.json', $out_extensions);

$out_types = \json_encode($out_types);
\file_put_contents(BIN_DIR . '/types.json', $out_types);

// And let's generate the library classes!

// Aliases.
$content = \file_get_contents(SKEL_DIR . '/templates/aliases.php');
$content = \str_replace(
	array(
		'ALIASES = null',
		'OFFICE = null',
	),
	array(
		'ALIASES = ' . json_to_php($out_aliases),
		'OFFICE = ' . json_to_php(\json_encode($out_office), true),
	),
	$content
);
\file_put_contents(OUT_DIR . '/aliases.php', $content);

// Extensions.
$content = \file_get_contents(SKEL_DIR . '/templates/extensions.php');
$content = \str_replace(
	array(
		'TYPES = null',
		'GZIP = null',
		'JSON = null',
		'OFFICE = null',
		'TEXT = null',
		'XML = null',
		'ZIP = null',
	),
	array(
		'TYPES = ' . json_to_php($out_extensions),
		'GZIP = ' . json_to_php(\json_encode($out_derived['gzip']), true),
		'JSON = ' . json_to_php(\json_encode($out_derived['json']), true),
		'OFFICE = ' . json_to_php(\json_encode($out_derived['office']), true),
		'TEXT = ' . json_to_php(\json_encode($out_derived['text']), true),
		'XML = ' . json_to_php(\json_encode($out_derived['xml']), true),
		'ZIP = ' . json_to_php(\json_encode($out_derived['zip']), true),
	),
	$content
);
\file_put_contents(OUT_DIR . '/extensions.php', $content);

// Types.
$content = \file_get_contents(SKEL_DIR . '/templates/types.php');
$content = \str_replace(
	'TYPES = null',
	'TYPES = ' . json_to_php($out_types),
	$content
);
\file_put_contents(OUT_DIR . '/types.php', $content);

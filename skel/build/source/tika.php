<?php
/**
 * Righteous MIMES: Apache Tika
 *
 * @see {https://raw.githubusercontent.com/apache/tika/master/tika-core/src/main/resources/org/apache/tika/mime/tika-mimetypes.xml}
 *
 * @copyright 2017 The Apache Software Foundation
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

namespace Righteous\Build\Source;

use Righteous\MIMEs;



class Tika extends \Righteous\Build\Source\FreeDesktop {
	/**
	 * Corresponding Source Flag
	 *
	 * @var int
	 */
	const FLAG = MIMEs::SOURCE_TIKA;
}

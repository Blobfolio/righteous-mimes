<?php
/**
 * Righteous MIMES: Type Aliases
 *
 * This data provides some reverse-lookup shortcuts when trying to
 * disambiguate type aliases.
 *
 * Note: This file is generated automatically; do not edit by hand
 * unless you plan to make a habit of it.
 *
 * @package blobfolio/righteous-mimes
 * @author	Blobfolio, LLC <hello@blobfolio.com>
 */

/**
 * Data Source: Apache
 *
 * @see {https://raw.githubusercontent.com/apache/httpd/trunk/docs/conf/mime.types}
 *
 * @copyright 2017 The Apache Software Foundation
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Data Source: Freedesktop.org
 *
 * @see {https://cgit.freedesktop.org/xdg/shared-mime-info/plain/freedesktop.org.xml.in}
 *
 * @copyright 2017 Freedesktop.org
 * @license https://opensource.org/licenses/MIT MIT
 */

/**
 * Data Source: IANA
 *
 * @see {https://www.iana.org/assignments/media-types}
 *
 * @copyright 2017 IETF Trust
 * @license https://www.rfc-editor.org/copyright/ rfc-copyright-story
 */

/**
 * Data Source: Nginx
 *
 * @see {http://hg.nginx.org/nginx/raw-file/default/conf/mime.types}
 *
 * @copyright 2017 NGINX Inc.
 * @license https://opensource.org/licenses/BSD-2-Clause BSD
 */

/**
 * Data Source: Apache Tika
 *
 * @see {https://raw.githubusercontent.com/apache/tika/master/tika-core/src/main/resources/org/apache/tika/mime/tika-mimetypes.xml}
 *
 * @copyright 2017 The Apache Software Foundation
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache
 */

namespace Righteous\MIMEs\Data;

final class Aliases {
	/**
	 * Type Aliases.
	 *
	 * This maps all explicitly recorded types to a primary type,
	 * whether the same type or some other associated type. This is
	 * error-prone, so authority scores accompany all results.
	 */
	const ALIASES = null;

	/**
	 * Office Aliases
	 *
	 * MS Office documents come in so many flavors with so many
	 * overlaps! This is a list of MIME types that are typically
	 * associated with any of them.
	 */
	const OFFICE = null;
}

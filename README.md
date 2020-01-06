# Righteous MIMEs!

**BETA WARNING:** This software is close but not yet ready for production use!

[![Build Status](https://travis-ci.org/Blobfolio/righteous-mimes.svg?branch=master)](https://travis-ci.org/Blobfolio/righteous-mimes)

PHP has a file type detection problem. Extensions like `FileInfo` and `ID3` rely on static data which is often incomplete, stale, or simply wrong, and their deducations frequently vary from method to method and server to server.

**Righteous MIMEs!** is a lightweight, stand-alone PHP library that augments PHP's native type detection capabilities (i.e. `fileinfo.so`) with numerous type-specific workarounds, extra magic parsing, and extensive type alias cross-referencing.

By increasing PHP's overall type awareness more than a magnitude, **Righteous MIMEs!** is able to make deductions about file types that are more accurate, complete, and consistent.

The table below explains the process in more detail:

| Native PHP | Righteous MIMEs! |
| ---- | ---- |
| :skull: :see_no_evil: | :skull: :mag: :surfer: |



&nbsp;
## Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Building](BUILDING.md)
4. [Reference](#reference)
   * [Definitions](#definitions)
   * [Working With Files](#working-with-files)
   * [General Helpers](#general-helpers)
5. [Bug/Type Reporting](#bugtype-reporting)
6. [License](#license)



&nbsp;
## Requirements

**Righteous MIMEs!** requires `PHP 7.3+` compiled with the following extensions (all of which are quite common):
 * `dom`
 * `fileinfo`
 * `filter`
 * `json`
 * `mbstring`
 * `xml`

While **Righteous MIMEs!** can technically be used on its own, it is highly recommended you add [getID3](https://github.com/JamesHeinrich/getID3) to your project as it allows **RM!** to fix a few additional type detection issues related to MP4 and OGG media.

If you're building atop a CMS like WordPress, just be careful not to override any bundled versions of `getID3` that might already be present (i.e. stick with their copy).



&nbsp;
## Installation

For most use cases, it is recommended to install **Righteous MIMEs!** using [Composer](https://getcomposer.org/):
```bash
# Assuming you want the latest and greatest "master" branch:
composer require "blobfolio/righteous-mimes:dev-master"
```

If you're doing something weird or want to integrate the library manually, all of the important files live inside the `lib/righteous/` directory.

The meat of **Righteous MIMEs!** is also available as a WordPress plugin called [Lord of the Files](https://wordpress.org/plugins/blob-mimes/). If you're just looking to fix Media Library upload issues like [#40175](https://core.trac.wordpress.org/ticket/40175), this plugin is your best bet!



&nbsp;
## Reference

This library comes with four main class files you may wish to interact with:
* `Righteous\MIMEs` contains all of the library constants.
* `Righteous\MIMEs\Extensions` contains some helper methods relating to file types and extensions.
* `Righteous\MIMEs\File` is used to analyze an individual file to determine its true type, validate its extension, etc.
* `Righteous\MIMEs\Sanitize` contains static helper methods to sanitize file extension and MIME type formatting.

Additional classes and methods exist, but are subject to change so it is not recommended you rely on them directly. But that said, if you find something useful you wish were stable, open a ticket and we'll consider promoting its status. :wink:


&nbsp;
### Definitions

**Righteous MIMEs!**, like every other major type-detection suite, takes a multi-tiered approach to file analysis that breaks down roughly into two categories: _naive_ and _magic_.

Naive analysis gathers all of the information it can using only a file's path. For example, a file named "image.jpg" uses the `jpg` file extension, which is primarily associated with the registered `image/jpeg` media type.

Of course, just because a file happens to be called "image.jpg" doesn't mean it actually _is_ a valid JPEG file, but it's a good place to start.

Magic analysis, by contrast, looks for clues within a file's _content_. It is called "magic" because ~~it sounds cool~~ the correct answer can be arrived at even in cases where a file has the wrong extension or no extension at all, as if by magic! Equally impressive, type determinations can usually be made after reading a small percentage of the total file, keeping things nice and efficient.

Magic analysis, like magic in general, is not infallable, but is better than nothing.


&nbsp;
### Working With Files

The heart of **Righteous MIMEs!** revolves around its tiered file analysis capabilities, all of which live within the `Righteous\MIMEs\File` class.

The following instance methods are available:


&nbsp;
#### File->__construct(_string_ `$path`) : _bool_

The main magic of **Righteous MIMEs!** sits behind the `Righteous\MIMEs\File` class. All you need to do is instantiate an object with a string path, then use the relevant class methods to extract the information you want.

**Parameters**

| Type | Description |
| ---- | ---- |
| _string_ | A file path. |

Any sort of path-like value will do, but **Righteous MIMEs!** can only work with what it's given. Information about remote, fragmentary, or unreadable paths will be based entirely on naive deductions (i.e. the file name).

**Returns**

This method returns `true` if any information whatsoever was discovered, or `false` on complete and utter failure.

**Example**

```php
if (false !== ($file = new \Righteous\MIMEs\File('/path/to/IMAGE.JPG'))) {
    // Do something with it.
    if ('image/jpeg' === $file->type()) {
        …
    }
}
```


&nbsp;
#### File->info() : _?array_

This is a catch-all method that delivers a lot of information in one go. In many ways it resembles PHP's native [pathinfo()](https://www.php.net/manual/en/function.pathinfo.php) method, but there are a few differences worth noting.

First and foremost, **Righteous MIMEs!** and PHP qualify "filename" and "extension" differently. **RM!** believes file extensions follow _file names_ (not just periods), and because it also knows what a _valid_ extension looks like, is able to sanitize and normalize the value.

This will probably make more sense with some examples:

| File | Key | `File->info()` | `pathinfo()` |
| ---- | ---- | ---- | ---- |
| `".htaccess"` | `filename` | `".htaccess"` | `""` |
| `".htaccess"` | `extension` | `""` | `"htaccess"` |
| `"IMAGE.JPEG"` | `filename` | `"IMAGE"` | `"IMAGE"` |
| `"IMAGE.JPEG"` | `extension` | `"jpeg"` | `"JPEG"` |

This method also includes two additional keys: `type` and `valid`.

**Returns**

This method returns `null` on failure, or an array in the following format:

| Type | Key | Description |
| ---- | ---- | ---- |
| _string_ | `dirname` | The parent directory. |
| _string_ | `basename` | The path's base name. |
| _string_ | `filename` | The file name (minus extension). |
| _string_ | `extension` | The file extension (lowercase). |
| _string_ | `type` | The file type. |
| _bool_ | `valid` | `true` if the extension matches the type, `false` otherwise. |

**Example**

```php
$file = new \Righteous\MIMEs\File('wolf.jpg');
if (null !== $info = $file->info()) {
    …
}
```


&nbsp;
#### File->basename(_bool_ `$suggested` = `false`) : _?string_

Return the path's (normalized) base name, like PHP's native [basename()](https://www.php.net/manual/en/function.basename.php) method.

By default, this method returns the base name corresponding to the file's actual path, but if `true` is passed, the _best_ base name — based on media type — is returned instead.

**Parameters**

| Type | Description | Default |
| ---- | ---- | ---- |
| _bool_ | Use the best, suggested value instead of the naive one. | `false` |

**Returns**

This returns the base name as a _string_ or `null` if the path is invalid.

**Example**

```php
// Say you have a PNG image incorrectly named "wolf.jpg".
$file = new \Righteous\MIMEs\File('wolf.jpg');

echo $file->basename(); //-> "wolf.jpg"
echo $file->basename(false); //-> "wolf.jpg"
echo $file->basename(true); //-> "wolf.png"
```


&nbsp;
#### File->dirname() : _?string_

Return the path's parent directory exactly like PHP's native [dirname()](https://www.php.net/manual/en/function.dirname.php) method does.

**Returns**

This returns the parent directory as a _string_ or `null` if the path is invalid.

**Example**

```php
$file = new \Righteous\MIMEs\File('wolf.jpg');
echo $file->dirname(); //-> "."

$file = new \Righteous\MIMEs\File('/tmp/working/presentation.docx');
echo $file->dirname(); //-> "/tmp/working"
```


&nbsp;
#### File->extension(_bool_ `$suggested` = `false`) : _?string_

Return either the path's current extension, or if `true` is passed, the most appropriate extension given the content type (which may or may not be the same thing).

**Parameters**

| Type | Description | Default |
| ---- | ---- | ---- |
| _bool_ | Use the best, suggested value instead of the naive one. | `false` |

**Returns**

This returns the extension as a _string_ or `null` if the path is invalid.

Note: the formatting of the return values may not be what you expect. See [File->info()](#file-info--array) for additional information.

**Example**

```php
// Say you have a PNG image incorrectly named "wolf.jpg".
$file = new \Righteous\MIMEs\File('wolf.jpg');

echo $file->extension(); //-> "jpg"
echo $file->extension(false); //-> "jpg"
echo $file->extension(true); //-> "png"
```


&nbsp;
#### File->filename() : _?string_

Return the path's file name (minus extension).

**Returns**

This returns the file name (minus extension) as a _string_ or `null` if the path is invalid.

Note: the formatting of the return values may not be what you expect. See [File->info()](#file-info--array) for additional information.

**Example**

```php
$file = new \Righteous\MIMEs\File('wolf.jpg');
echo $file->filename(); //-> "wolf"
```


&nbsp;
#### File->suggested() : _?array_

This method suggests base names based on the (naive) file name and (magic) content type. The array keys are the base names and the values are bitwise integers representing the sources that agree with the result.

See [Extensions::source()](#extensionssourcestring-ext-string-type--int) for more information about source values.

**Returns**

If the type and extension are already in agreement, the current value (and its source) are returned, otherwise suitable alternatives arranged by descending levels of certainty, if any, are returned. On failure, `null` is returned instead.

**Example**

```php
// Say you have a JPEG image incorrectly named "wolf.png".
$file = new \Righteous\MIMEs\File('wolf.png');
\print_r($file->suggested());
/*
    "wolf.jpg": 252,
    "wolf.jpeg": 212,
    "wolf.jpe": 3,
    …
*/
```


&nbsp;
#### File->type() : _?string_

Return the _best_ media type associated with a file.

**Returns**

This returns the media type as a _string_ or `null` if the path is invalid.

**Example**

```php
$file = new \Righteous\MIMEs\File('wolf.jpg');
echo $file->type(); //-> "image/jpeg"
```


&nbsp;
### General Helpers

**Righteous MIMEs!** includes a number of useful methods for more general tasks like formatting and sanitization.


&nbsp;
#### Extensions::primary_type(_string_ `$ext`) : _?string_

Return the primary MIME type associated with a given file extension.

**Parameters**

| Type | Description |
| ---- | ---- |
| _string_ | A file extension. |

**Returns**

Returns a MIME type as a _string_ or `null` if none comes to mind.

**Example**

```php
$type = \Righteous\MIMEs\Extensions::primary_type('jpg'); //-> "image/jpeg"
```


&nbsp;
#### Extensions::source(_string_ `$ext`, _string_ `$type`) : _int_

Return a bitwise _integer_ reflecting the source(s) that reference a relationship between a given extension and type.

The following source constants are defined in the `Righteous\MIMEs` class:

| Constant | Description | License | Link |
| ---- | ---- | ---- | ---- |
| `SOURCE_ALIAS` | This indicates an association should only be used for cross-referencing purposes (because it is an alias). | | |
| `SOURCE_APACHE` | Apache. | [Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0) | [Data](https://raw.githubusercontent.com/apache/httpd/trunk/docs/conf/mime.types) |
| `SOURCE_BLOBFOLIO` | Our own data! | [WTFPL](http://www.wtfpl.net/) | |
| `SOURCE_DRUPAL` | Drupal | [GPL](https://www.drupal.org/about/licensing) | [Data](https://raw.githubusercontent.com/drupal/drupal/8.8.x/core/lib/Drupal/Core/File/MimeType/ExtensionMimeTypeGuesser.php) |
| `SOURCE_FREEDESKTOP` | FreeDesktop.org. | [MIT](https://opensource.org/licenses/MIT) | [Data](https://cgit.freedesktop.org/xdg/shared-mime-info/plain/freedesktop.org.xml.in) |
| `SOURCE_IANA` | IANA. | [Misc](https://www.rfc-editor.org/copyright/) | [Data](https://www.iana.org/assignments/media-types) |
| `SOURCE_NGINX` | Nginx. | [BSD-2](https://opensource.org/licenses/BSD-2-Clause) | [Data](http://hg.nginx.org/nginx/raw-file/default/conf/mime.types) |
| `SOURCE_TIKA` | Apache "Tika". | [Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0) | [Data](https://raw.githubusercontent.com/apache/tika/master/tika-core/src/main/resources/org/apache/tika/mime/tika-mimetypes.xml) |
| `SOURCE_WORDPRESS` | WordPress | [GPLv2](https://wordpress.org/about/license/) | [Data](https://raw.githubusercontent.com/WordPress/WordPress/master/wp-includes/functions.php) |

**Parameters**

| Type | Description |
| ---- | ---- |
| _string_ | A file extension. |
| _string_ | A MIME type. |

**Returns**

This method always returns an _integer_. A value of `0` indicates no primary source references.

**Example**

```php
$source = \Righteous\MIMEs\Extensions::source('jpg', 'image/jpeg'); //-> 252

// Apache mentions it.
if (\Righteous\MIMEs::SOURCE_APACHE & $source) {
    …
}

// IANA mentions it.
if (\Righteous\MIMEs::SOURCE_IANA & $source) {
    …
}

// Etc.
```


&nbsp;
#### Extensions::verify_extension_type(_string_ `$ext`, _string_ `$type`) : _bool_

Determine whether or not a given file extension and media type belong together.

**Parameters**

| Type | Description |
| ---- | ---- |
| _string_ | A file extension. |
| _string_ | A MIME type. |

**Returns**

A value of `true` is returned if the file extension and media type belong together, otherwise `false`.

**Example**

```php
\Righteous\MIMEs\Extensions::verify_extension_type(
    'jpg',
    'image/jpeg'
); //-> true

\Righteous\MIMEs\Extensions::verify_extension_type(
    'jpeg',
    'image/jpeg'
); //-> true

\Righteous\MIMEs\Extensions::verify_extension_type(
    'jpe',
    'image/jpeg'
); //-> true

\Righteous\MIMEs\Extensions::verify_extension_type(
    'png',
    'image/jpeg'
); //-> false
```


&nbsp;
#### Sanitize::extension(_string_ `$ext`, _int_ `$flags`) : _?string_

Sanitize a file extension, ensuring it consists of valid characters and is in a neutral lowercase.

This method can also be used to parse a file's extension from a full path or name, though you should read the notes for [File->info()](#file-info--array) as there are a few quirks to consider.

**Parameters**

| Type | Description | Default |
| ---- | ---- | ---- |
| _string_ | A path, file name, or extension. | |
| _int_ | One or more bitwise filter flags. | `0` |

**Flags**

The following filter constants are defined in the `Righteous\MIMEs` class:

| Constant | Description |
| ---- | ---- |
| `FILTER_NO_UNKNOWN` | Reject any extension for which we have _no_ references whatsoever. |

**Returns**

This returns a normalized and sanitized file extension as a _string_ or `null` if none.

**Example**

```php
echo \Righteous\MIMEs\Sanitize::extension('IMAGE.JPEG'); //-> "jpeg"

echo \Righteous\MIMEs\Sanitize::extension('png'); //-> "png"

echo \Righteous\MIMEs\Sanitize::extension('fakeo'); //-> "fakeo"

echo \Righteous\MIMEs\Sanitize::extension(
    'fakeo',
    \Righteous\MIMEs::FILTER_NO_UNKNOWN
); //-> null
```


&nbsp;
#### Sanitize::type(_string_ `$type`, _int_ `$flags`) : _?string_

Sanitize a file/media/MIME type, ensuring it is formatted correctly, contains only valid characters, etc.

**Parameters**

| Type | Description | Default |
| ---- | ---- | ---- |
| _string_ | A MIME type. | |
| _int_ | One or more bitwise filter flags. | `0` |

**Flags**

The following filter constants are defined in the `Righteous\MIMEs` class:

| Constant | Description |
| ---- | ---- |
| `FILTER_NO_ALIAS` | Reject unknown, unofficial, or outdated media types. |
| `FILTER_NO_DEFAULT` | Reject `application/octet-stream`. |
| `FILTER_NO_EMPTY` | Reject `inode/x-empty`. |
| `FILTER_NO_UNKNOWN` | Reject any type for which we have _no_ references whatsoever. |
| `FILTER_UPDATE_ALIAS` | Replace an unofficial type with an official one whenever possible. |

Note: when `FILTER_UPDATE_ALIAS` is combined with `FILTER_NO_ALIAS`, replacement will be attempted first, and evaluation second.

**Returns**

This returns a normalized and sanitized media type as a _string_ or `null` if none.

**Example**

```php
echo \Righteous\MIMEs\Sanitize::type('image/x-bmp'); //-> "image/x-bmp"

echo \Righteous\MIMEs\Sanitize::type(
    'image/x-bmp',
    \Righteous\MIMEs::FILTER_NO_ALIAS
); //-> null

echo \Righteous\MIMEs\Sanitize::type(
    'image/x-bmp',
    \Righteous\MIMEs::FILTER_UPDATE_ALIAS
); //-> "image/bmp"
```


&nbsp;
## Bug/Type Reporting

MIME type detection is an endless game of cat and mouse, _and your help is needed!_

If you ever happen to find instances where an up-to-date **Righteous MIMEs!** incorrectly identifies a file types (or does something silly like suggest it be renamed), please open a ticket and report the issue.

Thank you very much!



&nbsp;
## License

Copyright © 2020 [Blobfolio, LLC](https://blobfolio.com) &lt;hello@blobfolio.com&gt;

This work is free. You can redistribute it and/or modify it under the terms of the Do What The Fuck You Want To Public License, Version 2.

    DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
    Version 2, December 2004
    
    Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>
    
    Everyone is permitted to copy and distribute verbatim or modified
    copies of this license document, and changing it is allowed as long
    as the name is changed.
    
    DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
    TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
    
    0. You just DO WHAT THE FUCK YOU WANT TO.

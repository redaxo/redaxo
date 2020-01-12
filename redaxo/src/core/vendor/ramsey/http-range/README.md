# ramsey/http-range

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

ramsey/http-range is a PHP library for parsing and handling [HTTP range requests][].

This project adheres to a [Contributor Code of Conduct][conduct]. By participating in this project and its community, you are expected to uphold this code.


## Installation

The preferred method of installation is via [Packagist][] and [Composer][]. Run
the following command to install the package and add it as a requirement to
your project's `composer.json`:

```bash
composer require ramsey/http-range
```


## Examples

ramsey/http-range is designed to be used with [PSR-7][] `RequestInterface`
objects. Assuming that `$request` in the following example conforms to this
interface, the following example shows how to use this library to parse an HTTP
`Range` header.

The following HTTP request uses a `Range` header to request the first 500 bytes
of the resource at `/image/1234`.

``` http
GET /image/1234 HTTP/1.1
Host: example.com
Range: bytes=0-499
```

When receiving a request like this, you can parse the `Range` header using the
following.

``` php
use Ramsey\Http\Range\Exception\NoRangeException;
use Ramsey\Http\Range\Range;

$filePath = '/path/to/image/1234.jpg';
$filePieces = [];

$range = new Range($request, filesize($filePath));

try {
    // getRanges() always returns an iterable collection of range values,
    // even if there is only one range, as is the case in this example.
    foreach ($range->getUnit()->getRanges() as $rangeValue) {
        $filePieces[] = file_get_contents(
            $filePath,
            false,
            null,
            $rangeValue->getStart(),
            $rangeValue->getLength()
        );
    }
} catch (NoRangeException $e) {
    // This wasn't a range request or the `Range` header was empty.
}
```


## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details.


## Copyright and License

The ramsey/http-range library is copyright © [Ben Ramsey](https://benramsey.com/) and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.


[http range requests]: https://tools.ietf.org/html/rfc7233
[conduct]: https://github.com/ramsey/http-range/blob/master/CODE_OF_CONDUCT.md
[packagist]: https://packagist.org/packages/ramsey/http-range
[composer]: http://getcomposer.org/
[psr-7]: http://www.php-fig.org/psr/psr-7/
[contributing]: https://github.com/ramsey/http-range/blob/master/CONTRIBUTING.md

[badge-source]: http://img.shields.io/badge/source-ramsey/http--range-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/ramsey/http-range.svg?style=flat-square
[badge-release]: https://img.shields.io/github/release/ramsey/http-range.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-build]: https://img.shields.io/travis/ramsey/http-range/master.svg?style=flat-square
[badge-coverage]: https://img.shields.io/coveralls/ramsey/http-range/master.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/ramsey/http-range.svg?style=flat-square

[source]: https://github.com/ramsey/http-range
[release]: https://packagist.org/packages/ramsey/http-range
[license]: https://github.com/ramsey/http-range/blob/master/LICENSE
[build]: https://travis-ci.org/ramsey/http-range
[coverage]: https://coveralls.io/r/ramsey/http-range?branch=master
[downloads]: https://packagist.org/packages/ramsey/http-range

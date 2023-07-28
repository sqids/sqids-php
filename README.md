# [Sqids PHP](https://sqids.org/php)

> 🚧 This is a work in progress and not ready for production yet.

Sqids (pronounced "squids") is a small library that lets you generate YouTube-looking IDs from numbers. It's good for link shortening, fast & URL-safe ID generation and decoding back into numbers for quicker database lookups.

## Getting started

Require this package, with [Composer](https://getcomposer.org), in the root directory of your project.

```bash
composer require sqids/sqids
```

Then you can import the class into your application:

```php
use Sqids\Sqids;
$sqids = new Sqids();
```

> **Note** Sqids require either [`bcmath`](https://secure.php.net/manual/en/book.bc.php) or [`gmp`](https://secure.php.net/manual/en/book.gmp.php) extension in order to work.

## Examples

Simple encode & decode:

```php
$sqids = new Sqids();
$id = $sqids->encode([1, 2, 3]); // "8QRLaD"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

Randomize IDs by providing a custom alphabet:

```php
$sqids = new Sqids('FxnXM1kBN6cuhsAvjW3Co7l2RePyY8DwaU04Tzt9fHQrqSVKdpimLGIJOgb5ZE');
$id = $sqids->encode([1, 2, 3]); // "B5aMa3"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

Enforce a *minimum* length for IDs:

```php
$sqids = new Sqids('', 10);
$id = $sqids->encode([1, 2, 3]); // "75JT1cd0dL"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

Prevent specific words from appearing anywhere in the auto-generated IDs:

```php
$sqids = new Sqids('', 10, ['word1', 'word2']);
$id = $sqids->encode([1, 2, 3]); // "8QRLaD"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

## License

[MIT](LICENSE)

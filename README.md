# [Sqids PHP](https://sqids.org/php)

[![Build Status](https://img.shields.io/github/actions/workflow/status/sqids/sqids-php/phpunit.yml?label=tests)](https://github.com/sqids/sqids/actions)
[![Monthly Downloads](https://img.shields.io/packagist/dm/sqids/sqids)](https://packagist.org/packages/sqids/sqids/stats)
[![Latest Version](https://img.shields.io/packagist/v/sqids/sqids)](https://packagist.org/packages/sqids/sqids)

[Sqids](https://sqids.org/php) (*pronounced "squids"*) is a small library that lets you **generate unique IDs from numbers**. It's good for link shortening, fast & URL-safe ID generation and decoding back into numbers for quicker database lookups.

Features:

- **Encode multiple numbers** - generate short IDs from one or several non-negative numbers
- **Quick decoding** - easily decode IDs back into numbers
- **Unique IDs** - generate unique IDs by shuffling the alphabet once
- **ID padding** - provide minimum length to make IDs more uniform
- **URL safe** - auto-generated IDs do not contain common profanity
- **Randomized output** - Sequential input provides nonconsecutive IDs
- **Many implementations** - Support for [40+ programming languages](https://sqids.org/)

## 🧰 Use-cases

Good for:

- Generating IDs for public URLs (eg: link shortening)
- Generating IDs for internal systems (eg: event tracking)
- Decoding for quicker database lookups (eg: by primary keys)

Not good for:

- Sensitive data (this is not an encryption library)
- User IDs (can be decoded revealing user count)

## 🚀 Getting started

Require this package, with [Composer](https://getcomposer.org), in the root directory of your project.

```bash
composer require sqids/sqids
```

Then you can import the class into your application:

```php
use Sqids\Sqids;
$sqids = new Sqids();
```

> [!IMPORTANT]
> Sqids require either the [`bcmath`](https://secure.php.net/manual/en/book.bc.php) or [`gmp`](https://secure.php.net/manual/en/book.gmp.php) extension in order to work.

## 👩‍💻 Examples

Simple encode & decode:

```php
$sqids = new Sqids();
$id = $sqids->encode([1, 2, 3]); // "86Rf07"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

> [!NOTE]
> 🚧 Because of the algorithm's design, **multiple IDs can decode back into the same sequence of numbers**. If it's important to your design that IDs are canonical, you have to manually re-encode decoded numbers and check that the generated ID matches.

Enforce a *minimum* length for IDs:

```php
$sqids = new Sqids(minLength: 10);
$id = $sqids->encode([1, 2, 3]); // "86Rf07xd4z"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

Randomize IDs by providing a custom alphabet:

```php
$sqids = new Sqids(alphabet: 'FxnXM1kBN6cuhsAvjW3Co7l2RePyY8DwaU04Tzt9fHQrqSVKdpimLGIJOgb5ZE');
$id = $sqids->encode([1, 2, 3]); // "B4aajs"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

Prevent specific words from appearing anywhere in the auto-generated IDs:

```php
$sqids = new Sqids(blocklist: ['86Rf07']);
$id = $sqids->encode([1, 2, 3]); // "se8ojk"
$numbers = $sqids->decode($id); // [1, 2, 3]
```

## 📝 License

[MIT](LICENSE)

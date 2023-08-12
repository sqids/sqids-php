# CHANGELOG

**v0.3.0:**
- Bug fix: test for decoding an invalid ID with a repeating reserved character
- Removing requirement of `ext-mbstring`

**v0.2.0:**
- Making the public constant `DEFAULT_BLOCKLIST` available
- Removed `mb_` functions, because the spec does not guarantee unicode support

**v0.1.0:**
- Initial implementation of the [Sqids spec](https://github.com/sqids/sqids-spec)

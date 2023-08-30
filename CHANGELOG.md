# CHANGELOG

**v0.3.1:**
- Bug fix: spec update (PR #7): blocklist filtering in uppercase-only alphabet [[PR #7](https://github.com/sqids/sqids-spec/pull/7)]

**v0.3.0:**
- Bug fix: test for decoding an invalid ID with a repeating reserved character
- Removing requirement of `ext-mbstring`

**v0.2.0:**
- Making the public constant `DEFAULT_BLOCKLIST` available
- Removed `mb_` functions, because the spec does not guarantee unicode support

**v0.1.0:**
- Initial implementation of the [Sqids spec](https://github.com/sqids/sqids-spec)

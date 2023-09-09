# CHANGELOG

**v0.4.0:** **⚠️ BREAKING CHANGE**
- **Breaking change**: IDs change. Algorithm has been fine-tuned for better performance [[Issue #11](https://github.com/sqids/sqids-spec/issues/11)]
- `alphabet` cannot contain multibyte characters
- `minLength` upper limit has increased from alphabet length to `255`
- Max blocklist re-encoding attempts has been capped at the length of the alphabet - 1
- Minimum alphabet length has changed from 5 to 3
- `minValue()` and `maxValue()` functions have been removed
- Max integer encoding value is `PHP_INT_MAX`

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

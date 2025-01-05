# CHANGELOG

**v0.5.0:** **⚠️ BREAKING CHANGE**
- **Breaking change:** A bug fix in the `isBlockedId` function resolves an issue where ID regeneration was triggered when a blocked word containing integers was found in the middle of the generated ID. For example, if a blocked word was `def2` and the generated ID was `abc1def2ghi3`, the ID would have been regenerated, even though it shouldn't have been according to [the spec](https://github.com/sqids/sqids-spec). Although this scenario is rare with the default blocklist, it is considered a breaking change. Since this repository is pre-1.0, only the minor version is incremented. Commit [a818ed](https://github.com/sqids/sqids-php/commit/a818ed4a25810b25663ece2354b0d6a2cc129088)
- Lots of performance optimizations in PR [#17](https://github.com/sqids/sqids-php/pull/17) and [#18](https://github.com/sqids/sqids-php/pull/18) thanks to [@GromNaN](https://github.com/GromNaN)
- Running tests for PHP 8.4

**v0.4.1:**
- Removed testing for `uniques` ([part of the spec](https://github.com/sqids/sqids-spec/blob/main/tests/internal/uniques.test.ts))
- Support for PHP 8.1 [[PR #8](https://github.com/sqids/sqids-php/pull/8)]

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

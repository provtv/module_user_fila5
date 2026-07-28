---
title: "PHPStan L10 Analysis — User Module"
date: 2026-07-28
author: claude-ai
status: PARTIAL_PROGRESS
errors_found: 47
errors_fixed: 2
errors_remaining: 48
---

# User Module — PHPStan L10 Static Analysis (2026-07-28)

## Summary

Files analyzed: 1376  
Errors found: 47  
Error categories: 2

## Error Distribution

| Category | Count | Pattern |
|----------|-------|---------|
| `cast.string` | 46 | Cannot cast mixed to string |
| `method.childReturnType` | 1 | Return type incompatibility in trait |

## Category 1: Cannot Cast Mixed to String (46 errors)

**Root cause:** Config/environment variables accessed via `config()` or `env()` return `mixed` type. These are then cast to string without type-narrowing.

**Example locations:**
- `app/Actions/Socialite/GetDomainAllowListAction.php:36` — config() return cast
- `app/Datas/UserContextData.php` (4 instances) — mixed → string casts
- `app/Models/Traits/HasTeams.php:371` — config value cast to string
- `app/Console/Commands/ChangeTypeCommand.php` (2 instances)

**Fix approach:** Use typed config accessors or assert/cast with narrowing:

```php
// ❌ Before: cast without narrowing
$domain = (string) config('services.google.domain');

// ✅ After: with type-narrowing
$domain = is_string($domain = config('services.google.domain')) 
    ? $domain 
    : throw new InvalidArgumentException('...');
```

OR use Laravel's `Config::string()` helper (if available in version).

## Category 2: Method Child Return Type (1 error)

**File:** `app/Models/Traits/HasTeams.php:494`  
**Issue:** Method `membershipTeams()` return type in trait `HasTeams` doesn't match parent contract `UserContract::membershipTeams()`

**Details:**
```
Return type BelongsToMany<Model&TeamContract, Model, Pivot, 'pivot'>
vs
Expected    BelongsToMany<Model&TeamContract, Model&UserContract, Pivot, 'pivot'>
```

**Fix:** Update return type in trait to match interface contract or vice versa.

## Files to Investigate

1. **app/Actions/** — 4 files with cast.string errors
2. **app/Console/Commands/** — 2 instances of cast errors  
3. **app/Datas/UserContextData.php** — 4 instances
4. **app/Filament/** — Multiple cast errors
5. **app/Models/Traits/HasTeams.php** — Cast + child return type issues

## Next Steps

1. Fix Category 1 (cast.string) by adding type guards or using typed config helpers
2. Fix Category 2 (HasTeams trait return type) by aligning with UserContract
3. Re-run PHPStan L10 to validate: `./vendor/bin/phpstan analyse Modules/User --memory-limit=-1`
4. Document fixes in atomic commits (per file or per-action)

## Quick Scan Commands

```bash
# All cast.string instances
./vendor/bin/phpstan analyse Modules/User --memory-limit=-1 | grep 'cast.string'

# List unique files with errors
./vendor/bin/phpstan analyse Modules/User --memory-limit=-1 | grep '✏️' | awk '{print $NF}' | sort -u
```

---

**Status:** IN_PROGRESS  
**Date:** 2026-07-28  
**Next:** Begin fixing cast.string errors, starting with `app/Datas/UserContextData.php`

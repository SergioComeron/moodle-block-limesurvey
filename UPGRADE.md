# Upgrade Notes - LimeSurvey Block v2.0

## Summary of Changes

This version includes a major security refactor and code improvements:

### Security Improvements ✅
- **Removed direct PHP endpoint** (`api_fetch_script.php` is now deprecated)
- **Implemented Moodle External API** for secure AJAX calls
- **Added proper capability checks** in the external service
- **CSP compliant** - No more inline scripts

### Code Quality Improvements
- **Fixed bug**: Variable `$participant` was used outside its loop (line 134 in old version)
- **Removed debug logs** from production code
- **Added proper PHPDoc** to all functions
- **Follows Moodle coding standards**

### Architecture Changes
- Uses AMD/AJAX instead of direct fetch to PHP file
- Implements `db/services.php` for web service definition
- New external API class: `block_limesurvey\external\get_surveys`
- JavaScript module: `block_limesurvey/surveys`

## What You Need to Do

### 1. Upgrade Plugin Version

After pulling the new code, run:

```bash
php admin/cli/upgrade.php
```

Or visit: **Site Administration → Notifications** and click "Upgrade Moodle database now"

### 2. Verify JavaScript Compilation

The AMD JavaScript has been pre-compiled, but if you make changes to `amd/src/surveys.js`, you need to recompile:

```bash
# Using Grunt (requires Node.js 22.x)
npx grunt amd

# Or if you have Moodle's build tools
php admin/cli/build_amd.php --file=blocks/limesurvey/amd/src/surveys.js
```

### 3. (Optional) Remove Old File

The file `api_fetch_script.php` is no longer used and can be safely deleted:

```bash
rm blocks/limesurvey/api_fetch_script.php
```

### 4. Clear Caches

```bash
php admin/cli/purge_caches.php
```

Or via UI: **Site Administration → Development → Purge all caches**

### 5. Test the Block

1. Go to your Dashboard
2. The LimeSurvey block should load surveys via AJAX
3. Check browser console - should see no errors
4. Verify surveys display correctly with ✅/⬜️ icons

## Configuration

No configuration changes required. Your existing settings will continue to work:
- API URL
- API Username
- API Password
- Extra Attributes

## Troubleshooting

### Surveys not loading?

1. Check browser console for JavaScript errors
2. Verify external service is registered:
   - Go to: **Site Administration → Server → Web services → External services**
   - You should see `block_limesurvey_get_surveys` function available

3. Check capability:
   - Users need `block/limesurvey:myaddinstance` capability

### "Function not found" error?

Run the upgrade script again:
```bash
php admin/cli/upgrade.php
```

This ensures the new external service is registered.

## Rollback Instructions

If you need to rollback to the old version:

1. Checkout previous commit
2. Run upgrade to downgrade version number
3. Clear caches

**Note**: The old version has security issues. Rollback is not recommended.

## Breaking Changes

- `api_fetch_script.php` endpoint no longer exists
- JavaScript now uses AMD modules instead of inline scripts
- External service `block_limesurvey_get_surveys` must be enabled (auto-enabled)

## Developer Notes

### New Files Added:
- `db/services.php` - External service definition
- `classes/external/get_surveys.php` - External API implementation
- `amd/src/surveys.js` - AMD module source
- `amd/build/surveys.min.js` - Compiled AMD module
- `amd/build/surveys.min.js.map` - Source map

### Modified Files:
- `block_limesurvey.php` - Now uses AMD, added PHPDoc
- `lang/en/block_limesurvey.php` - Added new strings in English
- `version.php` - Updated to v2.0, bumped version number
- `db/access.php` - Minor formatting improvements

### Files to Delete (Optional):
- `api_fetch_script.php` - No longer used

## Support

For issues or questions, check:
- Browser console for JavaScript errors
- Moodle error logs: `admin/tool/log/index.php`
- PHP error logs on your server

---

**Version**: 2.0
**Date**: 2025-11-20
**Maturity**: BETA

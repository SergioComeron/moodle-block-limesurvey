# LimeSurvey Block for Moodle

A Moodle block that displays active LimeSurvey surveys for the current user, with automatic caching, dynamic title formatting using participant attributes, and real-time survey completion tracking.

## Features

### Core Features
- **Secure Integration**: Uses Moodle External API with proper capability checks
- **Performance Optimized**: 24-hour cache reduces API calls and improves load times
- **Smart Cache**: Automatically clears cache when user completes a survey
- **Modern UI**: Card-based design with visual distinction between completed and pending surveys
- **Response Viewing**: Expandable sections to view completed survey responses
- **Real-time Status**: Shows survey completion status with visual indicators
- **Completion Percentage**: Displays progress with circular progress indicators for pending surveys
- **Expiration Alerts**: Visual badges showing when surveys expire (Today, 1 day, 7 days)

### Advanced Features (v2.17)
- **Dynamic Survey Titles**: Format survey titles using participant attributes
  - Example: `"Course Evaluation - Course: {attribute_12} - Professor: {attribute_9}"`
  - Automatically falls back to original title if attributes are missing
- **Custom Block Title**: Configure a custom title for the block
- **Flexible Attribute Support**: Request and display custom participant attributes from LimeSurvey
- **Debug Logging**: Optional detailed logging for troubleshooting API integration
- **Automatic Attribute Retrieval**: Uses `get_participant_properties()` to fetch all participant data

## Performance

The block uses Moodle's caching system with smart cache invalidation:

- **First load**: 2-3 seconds (fetches from LimeSurvey API)
- **Subsequent loads**: <0.1 seconds (cached data)
- **Cache duration**: 24 hours (86400 seconds)
- **Auto-refresh**: Cache automatically clears when user completes a survey

### How Smart Cache Works

1. User clicks on a pending survey → System marks this in browser session
2. User completes survey in LimeSurvey
3. User returns to Moodle dashboard → System detects return and clears cache
4. Block immediately shows updated status (completed survey)

This dramatically reduces load on both Moodle and LimeSurvey servers while ensuring users always see current survey status.

## Installation

1. Copy the plugin to `blocks/limesurvey/`
2. Visit **Site Administration → Notifications** to install
3. Configure the block settings (see Configuration section)

## Configuration

### Required Settings

Go to **Site Administration → Plugins → Blocks → LimeSurvey Block**

#### 1. LimeSurvey API URL
```
https://your-limesurvey-domain.com/index.php/admin/remotecontrol
```
The URL to your LimeSurvey JSON-RPC API endpoint.

#### 2. API Username
Username of a LimeSurvey user with API access and owner permissions for the surveys.

#### 3. API Password
Password for the API user.

### Optional Settings

#### 4. Extra Attributes
Comma-separated list of attribute numbers to retrieve from LimeSurvey participants.

**Format options:**
- Attribute numbers: `8, 12` or `8,12`
- With prefix: `attribute_8, attribute_12`
- Custom names: `nombre_profe, asignatura`

**Example:**
```
8, 12
```

#### 5. Custom Block Title
Leave empty to use the default "LimeSurvey Block" title, or enter a custom title.

**Example:**
```
My Active Surveys
```

#### 6. Survey Title Format
Custom format for survey titles using placeholders. Leave empty to use original survey titles.

**Available placeholders:**
- `{title}` - Original survey title
- `{attribute_N}` - Attribute N value (e.g., `{attribute_8}`, `{attribute_12}`)
- `{N}` - Short form (e.g., `{8}`, `{12}`)

**Important:** If any attribute is missing or empty for a survey, the original title will be used automatically.

**Example configurations:**

```
{title} - Course: {attribute_12} - Professor: {attribute_9}
```
Result: `"Course Evaluation - Course: Mathematics - Professor: John Doe"`

```
{title} ({attribute_8})
```
Result: `"Satisfaction Survey (Autumn 2024)"`

```
{attribute_12} - {title}
```
Result: `"Advanced Programming - End of Course Survey"`

#### 7. Debug Logging
Enable detailed logging for debugging purposes. When disabled, only errors are logged.

**Logs include:**
- API calls and responses
- Cache hits and misses
- Participant data
- Attribute retrieval
- Survey filtering decisions

**Log location:** Moodle error log or system logs (check `$CFG->debugdisplay` settings)

### LimeSurvey Setup

1. **Enable JSON-RPC API** in LimeSurvey global settings
2. **Create surveys** in LimeSurvey
3. **Activate the surveys** (Active = Yes)
4. **Add participants** with their Moodle email addresses
5. **Configure attributes** (if using dynamic titles):
   - Go to Survey Settings → Survey participants
   - Manage attributes for your survey
   - Add custom attributes (e.g., attribute_8, attribute_12)
   - Fill in values for each participant
6. **Set API user permissions**: Ensure the API user is the owner of the surveys or has appropriate permissions

### Moodle Setup

1. Configure the plugin settings (see above)
2. Add the block to a page:
   - Go to Dashboard (My Moodle)
   - Turn editing on
   - Add block → LimeSurvey Block
3. The block will automatically display surveys for the logged-in user

## Usage Examples

### Example 1: University Course Evaluations

**LimeSurvey setup:**
- attribute_9: Professor name
- attribute_12: Course name
- attribute_13: Semester

**Moodle configuration:**
```
Extra Attributes: 9, 12, 13
Survey Title Format: {attribute_12} - {attribute_9} ({attribute_13})
```

**Result:**
- Survey with attributes: `"Advanced Mathematics - Dr. Smith (Fall 2024)"`
- Survey without attributes: `"General Satisfaction Survey"` (original title)

### Example 2: Simple Format

**LimeSurvey setup:**
- attribute_8: Department

**Moodle configuration:**
```
Extra Attributes: 8
Survey Title Format: {title} - {attribute_8}
```

**Result:**
- Survey with attribute: `"Employee Satisfaction Survey - IT Department"`
- Survey without attribute: `"Employee Satisfaction Survey"` (original title)

## Cache Management

### Clear All Caches

Via CLI:
```bash
php admin/cli/purge_caches.php
```

Via Web Interface:
**Site Administration → Development → Purge all caches**

### Clear LimeSurvey Block Cache Only

**Site Administration → Plugins → Caching → Configuration**

Find "block_limesurvey / surveys" and click "Purge"

### Change Cache Duration

Edit `blocks/limesurvey/db/caches.php`:
```php
'ttl' => 86400, // 24 hours in seconds
```

Change to desired value:
- 1 hour: 3600
- 12 hours: 43200
- 24 hours: 86400
- 1 week: 604800

After changing, run:
```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

## Troubleshooting

### Surveys Not Appearing

**Check these items in order:**

1. **LimeSurvey API credentials**
   - Go to Block settings and verify URL, username, password
   - Test API URL by visiting it in browser (should show JSON-RPC endpoint info)

2. **Survey status in LimeSurvey**
   - Surveys must be active (Active = Yes)
   - Check start/end dates
   - Verify surveys haven't expired

3. **Participant configuration**
   - User's email in Moodle must match participant email in LimeSurvey exactly
   - Participant must exist in survey's participant table
   - Check token status (not used/used)

4. **API user permissions**
   - API user must be survey owner OR have appropriate permissions
   - Check LimeSurvey user settings

5. **Enable debug logging**
   - Turn on debug logging in block settings
   - Check Moodle error logs for messages starting with `LimeSurvey API -`
   - Look for error messages or empty responses

6. **Clear cache**
   ```bash
   php admin/cli/purge_caches.php
   ```

### Dynamic Titles Not Working

**Symptoms:** Survey titles don't show attribute values, show original titles instead.

**Solutions:**

1. **Enable debug logging** and check logs for:
   ```
   LimeSurvey API - get_participant_properties (TID=X, all props) response: {...}
   LimeSurvey API - Merged participant data: {...}
   LimeSurvey API - Missing or empty attribute for placeholder: {attribute_X}
   ```

2. **Verify attributes exist in LimeSurvey:**
   - Go to Survey → Survey participants
   - Click "Manage attributes"
   - Check that attribute_8, attribute_12, etc. are defined
   - Verify participant has values for those attributes

3. **Check attribute configuration:**
   - Extra Attributes field must include the attribute numbers
   - Example: `8, 12` or `attribute_8, attribute_12`

4. **Test placeholder format:**
   - Make sure Survey Title Format uses correct syntax: `{attribute_12}` not `{attribute_12`
   - Check for typos in attribute numbers

5. **Clear cache:**
   ```bash
   php admin/cli/purge_caches.php
   ```

### Cache Issues

If you're seeing outdated survey data:
```bash
php admin/cli/purge_caches.php
```

Or clear only LimeSurvey block cache via web interface (see Cache Management section).

### Debug Logs

Enable debug logging in block settings and check for messages starting with:
```
LimeSurvey API - Cache miss/hit
LimeSurvey API - Data cached for user
LimeSurvey API - get_participant_properties
LimeSurvey API - Merged participant data
```

**Log location:**
- Moodle error log: Check `$CFG->debugdisplay` settings
- Server error logs: Usually `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- PHP error logs: Check `php.ini` for `error_log` setting

## Technical Details

### Files Structure

```
blocks/limesurvey/
├── block_limesurvey.php          # Main block class
├── version.php                   # Plugin version info (v2.17)
├── settings.php                  # Admin settings page
├── db/
│   ├── access.php               # Capability definitions
│   ├── services.php             # External API service definition
│   └── caches.php               # Cache definition
├── classes/external/
│   ├── get_surveys.php          # External API with caching & attribute retrieval
│   └── clear_cache.php          # Cache clearing service
├── amd/
│   ├── src/surveys.js           # AMD module source (ES6)
│   └── build/surveys.min.js     # Compiled AMD module
├── lang/
│   ├── en/block_limesurvey.php  # English language strings
│   └── es/block_limesurvey.php  # Spanish language strings
├── jsonrpcphp-master/           # JSON-RPC PHP client library
└── README.md                    # Documentation
```

### Cache Definition

- **Mode**: Application cache (shared across sessions)
- **TTL**: 86400 seconds (24 hours)
- **Static Acceleration**: Enabled for in-memory caching
- **Key Format**: `user_{userid}_surveys`

### External API

Services available:

#### 1. `block_limesurvey_get_surveys`
- **Type**: Read-only
- **AJAX enabled**: Yes
- **Login required**: Yes
- **Capability**: `block/limesurvey:myaddinstance`
- **Returns**: Survey list for current user (cached)
- **Cache behavior**: Returns cached data if available, otherwise fetches from LimeSurvey API

#### 2. `block_limesurvey_clear_cache`
- **Type**: Write
- **AJAX enabled**: Yes
- **Login required**: Yes
- **Capability**: `block/limesurvey:myaddinstance`
- **Action**: Clears cache for current user
- **Use case**: Called automatically when user completes a survey

### Attribute Retrieval Process

1. **Configure attributes**: Admin sets attribute numbers in block settings (e.g., "8, 12")
2. **List participants**: Plugin calls `list_participants()` with attribute filter
3. **Get properties**: Plugin calls `get_participant_properties()` with TID or token to retrieve ALL participant data
4. **Merge data**: Attributes are merged into participant array
5. **Format title**: `format_survey_title()` replaces placeholders with attribute values
6. **Fallback**: If any attribute is missing/empty, uses original survey title

### LimeSurvey API Methods Used

- `get_session_key()` - Authentication
- `list_surveys()` - Get all surveys
- `list_participants()` - Get participants for a survey
- `get_participant_properties()` - Get detailed participant data including attributes
- `export_responses_by_token()` - Get survey responses
- `release_session_key()` - Cleanup

## Requirements

- Moodle 3.11 or higher
- LimeSurvey installation with JSON-RPC API enabled
- PHP 7.4 or higher
- cURL or fopen enabled in PHP

## Version History

- **v2.17** (2025-11-26):
  - Added dynamic survey title formatting with participant attributes
  - Added `get_participant_properties()` for attribute retrieval
  - Added fallback to original title when attributes are missing
  - Added custom block title configuration
  - Added debug logging option
  - Updated version to 2025112616

- **v2.16** (2025-11-24):
  - Improved completion percentage calculation
  - Added expiration badges and alerts
  - Enhanced UI with circular progress indicators
  - Better handling of partial responses

- **v2.2** (2025-11-20):
  - Added smart cache invalidation on survey completion

- **v2.1** (2025-11-20):
  - Added caching system (24-hour TTL)

- **v2.0** (2025-11-20):
  - Security refactor, External API, AMD modules

- **v1.0**:
  - Initial release

## License

GPL v3 or later

## Support

For issues or questions:

1. **Enable debug logging** in block settings
2. **Check browser console** for JavaScript errors (F12 → Console tab)
3. **Review Moodle error logs**: Site Administration → Reports → Logs
4. **Check PHP error logs** on your server
5. **Clear caches**: `php admin/cli/purge_caches.php`
6. **Verify configuration**: Double-check all settings in block configuration page

## Credits

Developed by Sergio Comerón, 2024-2025

Built with:
- Moodle External API
- LimeSurvey JSON-RPC API
- JSON-RPC PHP Client Library

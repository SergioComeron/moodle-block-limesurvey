# LimeSurvey Block for Moodle

A Moodle block that displays active LimeSurvey surveys for the current user, with automatic caching for optimal performance.

## Features

- **Secure Integration**: Uses Moodle External API with proper capability checks
- **Performance Optimized**: 24-hour cache reduces API calls and improves load times
- **Smart Cache**: Automatically clears cache when user completes a survey
- **Modern UI**: Card-based design with visual distinction between completed and pending surveys
- **Response Viewing**: Expandable sections to view completed survey responses
- **Real-time Status**: Shows survey completion status with visual indicators

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
3. Configure the block settings:
   - LimeSurvey API URL
   - API Username
   - API Password
   - Extra Attributes (optional, comma-separated)

## Configuration

### LimeSurvey Setup

1. Create surveys in LimeSurvey
2. Activate the surveys
3. Add participants with their Moodle email addresses
4. Ensure the API user has owner permissions for the surveys

### Moodle Setup

1. Go to **Site Administration → Plugins → Blocks → LimeSurvey Block**
2. Enter your LimeSurvey API credentials:
   ```
   API URL: https://your-limesurvey.com/index.php/admin/remotecontrol
   API Username: your_api_user
   API Password: your_password
   ```
3. Add the block to the Dashboard (My Moodle)

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

1. Check LimeSurvey API credentials in block settings
2. Verify the API user is the survey owner in LimeSurvey
3. Ensure surveys are active
4. Check participants exist with matching email addresses
5. Use the diagnostic tool: `blocks/limesurvey/test_api.php`

### Cache Issues

If you're seeing outdated survey data:
```bash
php admin/cli/purge_caches.php
```

### Debug Logs

Enable debug logging in Moodle and check for messages starting with:
```
LimeSurvey API - Cache miss/hit
LimeSurvey API - Data cached for user
```

## Technical Details

### Files Structure

```
blocks/limesurvey/
├── block_limesurvey.php          # Main block class
├── version.php                   # Plugin version info
├── db/
│   ├── access.php               # Capability definitions
│   ├── services.php             # External API service definition
│   └── caches.php               # Cache definition
├── classes/external/
│   └── get_surveys.php          # External API implementation with caching
├── amd/
│   ├── src/surveys.js           # AMD module source
│   └── build/surveys.min.js     # Compiled AMD module
├── lang/en/
│   └── block_limesurvey.php     # Language strings
└── test_api.php                 # Diagnostic tool
```

### Cache Definition

- **Mode**: Application cache (shared across sessions)
- **TTL**: 86400 seconds (24 hours)
- **Static Acceleration**: Enabled for in-memory caching
- **Key Format**: `user_{userid}_surveys`

### External API

Services available:
1. `block_limesurvey_get_surveys`
   - Type: Read-only
   - AJAX enabled: Yes
   - Login required: Yes
   - Capability: `block/limesurvey:myaddinstance`
   - Returns: Survey list for current user (cached)

2. `block_limesurvey_clear_cache`
   - Type: Write
   - AJAX enabled: Yes
   - Login required: Yes
   - Capability: `block/limesurvey:myaddinstance`
   - Action: Clears cache for current user

## Requirements

- Moodle 3.11 or higher
- LimeSurvey installation with JSON-RPC API enabled
- PHP 7.4 or higher

## Version History

- **v2.2** (2025-11-20): Added smart cache invalidation on survey completion
- **v2.1** (2025-11-20): Added caching system (24-hour TTL)
- **v2.0** (2025-11-20): Security refactor, External API, AMD modules
- **v1.0**: Initial release

## License

GPL v3 or later

## Support

For issues or questions:
- Check browser console for JavaScript errors
- Review Moodle error logs: `admin/tool/log/index.php`
- Check PHP error logs on your server
- Use the diagnostic tool: `blocks/limesurvey/test_api.php`

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for block_limesurvey.
 *
 * @package   block_limesurvey
 * @copyright 2024, Sergio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activesurveys'] = 'Active surveys:';
$string['api_password_desc'] = 'Password to connect to the LimeSurvey API.';
$string['api_password'] = 'API Password';
$string['api_token_desc'] = 'Enter the LimeSurvey API token to enable integration.';
$string['api_token'] = 'LimeSurvey API Token';
$string['api_url_desc'] = 'Enter the LimeSurvey API URL (e.g., https://your-limesurvey-domain/index.php/admin/remotecontrol).';
$string['api_url'] = 'LimeSurvey API URL';
$string['api_user_desc'] = 'Username to connect to the LimeSurvey API.';
$string['api_user'] = 'API Username';
$string['atributosextra'] = 'Extra attributes';
$string['atributosextra_desc'] = 'Optional. Enter attribute numbers separated by commas to display as additional text below survey titles (e.g., "8, 12"). Leave empty if not needed. Note: Survey title formatting will work independently of this setting.';
$string['block_title'] = 'Custom block title';
$string['block_title_desc'] = 'Custom title for the block. Leave empty to use the default block name.';
$string['cachedef_surveys'] = 'LimeSurvey survey data for users';
$string['completed'] = 'Completed survey';
$string['debug_logging'] = 'Enable debug logging';
$string['debug_logging_desc'] = 'Enable detailed logging for debugging purposes. When disabled, only errors will be logged.';
$string['error_config'] = 'Please configure the LimeSurvey block in the site administration.';
$string['error_config_url'] = 'Please configure the real LimeSurvey URL in the block settings.';
$string['error_connection'] = 'Error connecting to LimeSurvey.';
$string['error_loading'] = 'Error loading surveys.';
$string['error_session'] = 'Error obtaining session key: {$a}';
$string['hideresponses'] = 'Hide responses';
$string['limesurvey:myaddinstance'] = 'Add a new LimeSurvey block to the Dashboard';
$string['loading'] = 'Loading surveys...';
$string['nosurveys'] = 'You have no active surveys.';
$string['pending'] = 'Pending survey';
$string['pluginname'] = 'LimeSurvey Block';
$string['responses'] = 'Responses';
$string['surveyprogress'] = 'Survey Progress: {$a->completed} of {$a->total} completed';
$string['allcompleted'] = 'Great! You have completed all surveys';
$string['expiresin'] = 'Expires in {$a} days';
$string['expiresday'] = 'Expires in 1 day';
$string['expirestoday'] = 'Expires today!';
$string['expired'] = 'Expired';
$string['viewresponses'] = 'View responses';
$string['survey_title_format'] = 'Survey title format';
$string['survey_title_format_desc'] = 'Custom format for survey titles using placeholders. Available placeholders: {title} (original survey title), {attribute_N} (where N is the attribute number, e.g., {attribute_9}, {attribute_12}). Leave empty to use the original survey titles. Example: "{title} - Course: {attribute_12} - Professor: {attribute_9}"';
$string['startdate'] = 'Start';
$string['expiresdate'] = 'Expires';
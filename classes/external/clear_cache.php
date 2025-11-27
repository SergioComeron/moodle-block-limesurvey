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
 * External API for clearing user's LimeSurvey cache.
 *
 * @package   block_limesurvey
 * @copyright 2024, Sergio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_limesurvey\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External function to clear cache for current user.
 */
class clear_cache extends external_api {

    /**
     * Log debug messages if debug logging is enabled.
     *
     * @param string $message Message to log
     */
    private static function debug_log($message) {
        $debugenabled = get_config('block_limesurvey', 'debug_logging');
        if ($debugenabled) {
            error_log($message);
        }
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Clear cache for the current user.
     *
     * @return array
     */
    public static function execute() {
        global $USER;

        // Validate parameters.
        self::validate_parameters(self::execute_parameters(), []);

        // Validate context.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/limesurvey:myaddinstance', $context);

        // Clear the cache for this user.
        $cache = \cache::make('block_limesurvey', 'surveys');
        $cachekey = 'user_' . $USER->id . '_surveys';
        $cache->delete($cachekey);

        self::debug_log('LimeSurvey API - Cache cleared for user ' . $USER->id);

        return [
            'success' => true,
            'message' => 'Cache cleared successfully',
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }
}

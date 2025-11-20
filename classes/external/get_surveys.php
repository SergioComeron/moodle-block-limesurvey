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
 * External API for getting LimeSurvey surveys.
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
use external_multiple_structure;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/blocks/limesurvey/jsonrpcphp-master/src/org/jsonrpcphp/JsonRPCClient.php');

/**
 * External function to get surveys for current user.
 */
class get_surveys extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Get surveys for the current user.
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

        // Try to get cached data first.
        $cache = \cache::make('block_limesurvey', 'surveys');
        $cachekey = 'user_' . $USER->id . '_surveys';
        $cacheddata = $cache->get($cachekey);

        if ($cacheddata !== false) {
            error_log('LimeSurvey API - Using cached data for user ' . $USER->id);
            return $cacheddata;
        }

        error_log('LimeSurvey API - Cache miss, fetching from API for user ' . $USER->id);

        $apiurl = get_config('block_limesurvey', 'api_url');
        $apiuser = get_config('block_limesurvey', 'api_user');
        $apipassword = get_config('block_limesurvey', 'api_password');

        // Validate configuration.
        if (empty($apiurl) || empty($apiuser) || empty($apipassword)) {
            return [
                'success' => false,
                'message' => get_string('error_config', 'block_limesurvey'),
                'surveys' => [],
            ];
        }

        if (strpos($apiurl, 'your-limesurvey-domain') !== false) {
            return [
                'success' => false,
                'message' => get_string('error_config_url', 'block_limesurvey'),
                'surveys' => [],
            ];
        }

        try {
            $client = new \org\jsonrpcphp\JsonRPCClient($apiurl);
            $sessionkey = $client->get_session_key($apiuser, $apipassword);

            if (is_array($sessionkey)) {
                throw new \moodle_exception('error_session', 'block_limesurvey', '', json_encode($sessionkey));
            }

            $response = $client->list_surveys($sessionkey);
            $surveys = [];

            // Debug: Log all surveys from LimeSurvey.
            error_log('LimeSurvey API - list_surveys response: ' . json_encode($response));
            error_log('LimeSurvey API - User email: ' . $USER->email);

            if (is_array($response) && !empty($response) && isset($response[0]['sid'])) {
                $currentdate = time();
                $atributosconfig = get_config('block_limesurvey', 'atributosextra');
                $atributosarray = array_map('trim', explode(',', $atributosconfig));

                error_log('LimeSurvey API - Total surveys found: ' . count($response));
                error_log('LimeSurvey API - Current date: ' . date('Y-m-d H:i:s', $currentdate));

                foreach ($response as $survey) {
                    // Validate survey data.
                    if (!is_array($survey) || !isset($survey['active'], $survey['sid'])) {
                        error_log('LimeSurvey API - Skipping survey (invalid data): ' . json_encode($survey));
                        continue;
                    }

                    error_log('LimeSurvey API - Processing survey: ' . $survey['sid'] . ' - ' . ($survey['surveyls_title'] ?? 'No title'));
                    error_log('LimeSurvey API - Survey active: ' . $survey['active']);
                    error_log('LimeSurvey API - Survey dates: start=' . ($survey['startdate'] ?? 'null') . ', expires=' . ($survey['expires'] ?? 'null'));

                    // Check if survey is active and within date range.
                    if ($survey['active'] !== 'Y') {
                        error_log('LimeSurvey API - Survey ' . $survey['sid'] . ' is not active');
                        continue;
                    }

                    if (!empty($survey['startdate']) && strtotime($survey['startdate']) > $currentdate) {
                        error_log('LimeSurvey API - Survey ' . $survey['sid'] . ' has not started yet');
                        continue;
                    }

                    if (!empty($survey['expires']) && strtotime($survey['expires']) <= $currentdate) {
                        error_log('LimeSurvey API - Survey ' . $survey['sid'] . ' has expired');
                        continue;
                    }

                    // Get participants for this survey.
                    $participants = $client->list_participants(
                        $sessionkey,
                        $survey['sid'],
                        0,
                        5000,
                        false,
                        $atributosarray,
                        ['email' => $USER->email]
                    );

                    error_log('LimeSurvey API - Participants response for survey ' . $survey['sid'] . ': ' . json_encode($participants));

                    if (!is_array($participants) || empty($participants)) {
                        error_log('LimeSurvey API - No participants found for survey ' . $survey['sid']);
                        continue;
                    }

                    error_log('LimeSurvey API - Found ' . count($participants) . ' participants for survey ' . $survey['sid']);

                    // Process each participant.
                    foreach ($participants as $participant) {
                        if (!isset($participant['participant_info']['email']) ||
                            $participant['participant_info']['email'] !== $USER->email) {
                            continue;
                        }

                        $token = $participant['token'];

                        // Check if user has responded and get response data.
                        $responsedata = self::get_survey_response($client, $sessionkey, $survey['sid'], $token);

                        // Build survey URL.
                        $parsedurl = parse_url($apiurl);
                        $baseurl = $parsedurl['scheme'] . '://' . $parsedurl['host'] . $parsedurl['path'];
                        $baseurl = preg_replace('/\/index\.php.*/', '/index.php', $baseurl);
                        $surveyurl = $baseurl . '/survey?sid=' . $survey['sid'] . '&token=' . $token;

                        // Get extra attributes.
                        $extraattributes = [];
                        foreach ($atributosarray as $key) {
                            if (!empty($key) && isset($participant[$key]) && !in_array($participant[$key], $extraattributes)) {
                                $extraattributes[] = $participant[$key];
                            }
                        }

                        $surveys[] = [
                            'title' => $survey['surveyls_title'],
                            'url' => $surveyurl,
                            'completed' => $responsedata['completed'],
                            'attributes' => $extraattributes,
                            'responses' => $responsedata['responses'],
                            'responseid' => $responsedata['responseid'],
                            'raw_api_response' => $responsedata['raw_api_response'],
                            'decoded_data' => $responsedata['decoded_data'],
                            'startdate' => $survey['startdate'] ?? null,
                            'expires' => $survey['expires'] ?? null,
                        ];
                    }
                }
            }

            $client->release_session_key($sessionkey);

            $result = [
                'success' => true,
                'message' => '',
                'surveys' => $surveys,
            ];

            // Store in cache for 24 hours.
            $cache->set($cachekey, $result);
            error_log('LimeSurvey API - Data cached for user ' . $USER->id);

            return $result;

        } catch (\Exception $e) {
            debugging('LimeSurvey connection error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('error_connection', 'block_limesurvey'),
                'surveys' => [],
            ];
        }
    }

    /**
     * Get survey response data for a user.
     *
     * @param object $client JSON-RPC client
     * @param string $sessionkey Session key
     * @param int $surveyid Survey ID
     * @param string $token User token
     * @return array Array with completed status, responses data, and response ID
     */
    private static function get_survey_response($client, $sessionkey, $surveyid, $token) {
        $result = [
            'completed' => false,
            'responses' => [],
            'responseid' => null,
            'raw_api_response' => null,
            'decoded_data' => null,
        ];

        try {
            $responsesbytoken = $client->export_responses_by_token(
                $sessionkey,
                $surveyid,
                'json',
                $token,
                null,
                'all',
                0,
                5000
            );

            // Store raw API response for debugging.
            if (is_array($responsesbytoken)) {
                $result['raw_api_response'] = 'ARRAY: ' . json_encode($responsesbytoken);
                return $result;
            }

            if (is_string($responsesbytoken)) {
                $result['raw_api_response'] = 'STRING (base64 encoded)';
                $decoded = base64_decode($responsesbytoken, true);

                if ($decoded !== false && $decoded !== '') {
                    $result['completed'] = true;
                    $result['decoded_data'] = $decoded;

                    // Parse JSON responses.
                    $responsesjson = json_decode($decoded, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($responsesjson)) {
                        $responses = $responsesjson['responses'] ?? $responsesjson;

                        if (!empty($responses) && is_array($responses)) {
                            // Get first response (usually there's only one per token).
                            $firstresponse = reset($responses);

                            if (is_array($firstresponse)) {
                                // Store response ID if available.
                                $result['responseid'] = $firstresponse['id'] ?? $firstresponse['response_id'] ?? null;

                                // Filter and format responses (exclude metadata).
                                foreach ($firstresponse as $key => $value) {
                                    // Skip technical fields.
                                    if (in_array($key, ['id', 'token', 'submitdate', 'lastpage', 'startlanguage',
                                                        'seed', 'startdate', 'datestamp', 'ipaddr', 'refurl'])) {
                                        continue;
                                    }

                                    // Include question responses.
                                    if (!empty($value) && !is_array($value)) {
                                        $result['responses'][$key] = $value;
                                    }
                                }
                            }
                        }
                    } else {
                        $result['decoded_data'] = 'JSON PARSE ERROR: ' . json_last_error_msg();
                    }
                } else {
                    $result['raw_api_response'] = 'STRING (empty or failed base64 decode)';
                }
            } else {
                $result['raw_api_response'] = 'UNKNOWN TYPE: ' . gettype($responsesbytoken);
            }

            return $result;
        } catch (\Exception $e) {
            debugging('Error getting survey response: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $result['raw_api_response'] = 'EXCEPTION: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Error message if any'),
            'surveys' => new external_multiple_structure(
                new external_single_structure([
                    'title' => new external_value(PARAM_TEXT, 'Survey title'),
                    'url' => new external_value(PARAM_URL, 'Survey URL'),
                    'completed' => new external_value(PARAM_BOOL, 'Whether user has completed the survey'),
                    'attributes' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'Extra attribute value'),
                        'Extra attributes',
                        VALUE_OPTIONAL
                    ),
                    'responses' => new external_single_structure([], 'Survey responses as key-value pairs', VALUE_OPTIONAL),
                    'responseid' => new external_value(PARAM_INT, 'Response ID', VALUE_OPTIONAL),
                    'raw_api_response' => new external_value(PARAM_RAW, 'Raw API response for debugging', VALUE_OPTIONAL),
                    'decoded_data' => new external_value(PARAM_RAW, 'Decoded response data', VALUE_OPTIONAL),
                    'startdate' => new external_value(PARAM_TEXT, 'Survey start date', VALUE_OPTIONAL),
                    'expires' => new external_value(PARAM_TEXT, 'Survey expiration date', VALUE_OPTIONAL),
                ]),
                'List of surveys'
            ),
        ]);
    }
}

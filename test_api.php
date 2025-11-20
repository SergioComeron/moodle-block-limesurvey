<?php
/**
 * LimeSurvey API Diagnostic Script
 *
 * This script tests the connection to LimeSurvey API and shows detailed information
 * about surveys, permissions, and configuration.
 *
 * Usage: Access via browser: http://localhost/stable_405/blocks/limesurvey/test_api.php
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/jsonrpcphp-master/src/org/jsonrpcphp/JsonRPCClient.php');

// Require login.
require_login();

// Only allow admin users.
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Handle AJAX request for participants BEFORE any output.
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['sid'])) {
    require_sesskey();

    header('Content-Type: application/json');

    try {
        $sid = required_param('sid', PARAM_INT);

        $apiUrl = get_config('block_limesurvey', 'api_url');
        $apiUser = get_config('block_limesurvey', 'api_user');
        $apiPassword = get_config('block_limesurvey', 'api_password');
        $atributosExtra = get_config('block_limesurvey', 'atributosextra');

        $client = new \org\jsonrpcphp\JsonRPCClient($apiUrl);
        $sessionKey = $client->get_session_key($apiUser, $apiPassword);

        if (is_array($sessionKey)) {
            echo json_encode(['error' => 'Session key error: ' . json_encode($sessionKey)]);
            exit;
        }

        $atributosArray = array_map('trim', explode(',', $atributosExtra));

        $participants = $client->list_participants(
            $sessionKey,
            $sid,
            0,
            100,
            false,
            $atributosArray,
            ['email' => $USER->email]
        );

        $userFound = false;
        if (is_array($participants)) {
            foreach ($participants as $participant) {
                if (isset($participant['participant_info']['email']) &&
                    $participant['participant_info']['email'] === $USER->email) {
                    $userFound = true;
                    break;
                }
            }
        }

        echo json_encode([
            'participants' => $participants,
            'user_email' => $USER->email,
            'user_found' => $userFound
        ]);

        $client->release_session_key($sessionKey);

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

// Set page context.
$PAGE->set_context($context);
$PAGE->set_url('/blocks/limesurvey/test_api.php');
$PAGE->set_title('LimeSurvey API Diagnostic');

echo $OUTPUT->header();

echo '<h2>LimeSurvey API Diagnostic Tool</h2>';
echo '<p>Current user: ' . fullname($USER) . ' (' . $USER->email . ')</p>';

// Get configuration.
$apiUrl = get_config('block_limesurvey', 'api_url');
$apiUser = get_config('block_limesurvey', 'api_user');
$apiPassword = get_config('block_limesurvey', 'api_password');
$atributosExtra = get_config('block_limesurvey', 'atributosextra');

echo '<h3>1. Configuration Check</h3>';
echo '<table class="table table-bordered" style="max-width: 800px;">';
echo '<tr><th>Setting</th><th>Value</th><th>Status</th></tr>';

$configOk = true;

echo '<tr>';
echo '<td>API URL</td>';
echo '<td><code>' . htmlspecialchars($apiUrl) . '</code></td>';
if (empty($apiUrl) || strpos($apiUrl, 'your-limesurvey-domain') !== false) {
    echo '<td><span class="badge badge-danger">Not configured</span></td>';
    $configOk = false;
} else {
    echo '<td><span class="badge badge-success">OK</span></td>';
}
echo '</tr>';

echo '<tr>';
echo '<td>API Username</td>';
echo '<td><code>' . htmlspecialchars($apiUser) . '</code></td>';
echo '<td>' . (empty($apiUser) ? '<span class="badge badge-danger">Empty</span>' : '<span class="badge badge-success">OK</span>') . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>API Password</td>';
echo '<td><code>' . (empty($apiPassword) ? '(empty)' : str_repeat('*', 8)) . '</code></td>';
echo '<td>' . (empty($apiPassword) ? '<span class="badge badge-danger">Empty</span>' : '<span class="badge badge-success">OK</span>') . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Extra Attributes</td>';
echo '<td><code>' . htmlspecialchars($atributosExtra) . '</code></td>';
echo '<td><span class="badge badge-info">Optional</span></td>';
echo '</tr>';

echo '</table>';

if (!$configOk) {
    echo '<div class="alert alert-danger">Configuration is incomplete. Please configure the block in Site Administration.</div>';
    echo $OUTPUT->footer();
    exit;
}

// Test API connection.
echo '<h3>2. API Connection Test</h3>';

try {
    $client = new \org\jsonrpcphp\JsonRPCClient($apiUrl);

    echo '<p>Attempting to connect to: <code>' . htmlspecialchars($apiUrl) . '</code></p>';

    // Get session key.
    $sessionKey = $client->get_session_key($apiUser, $apiPassword);

    if (is_array($sessionKey)) {
        echo '<div class="alert alert-danger">';
        echo '<strong>Session Key Error:</strong><br>';
        echo '<pre>' . print_r($sessionKey, true) . '</pre>';
        echo '</div>';
        echo $OUTPUT->footer();
        exit;
    }

    echo '<p><span class="badge badge-success">Connected Successfully</span></p>';
    echo '<p>Session Key: <code>' . substr($sessionKey, 0, 20) . '...</code></p>';

    // List all surveys.
    echo '<h3>3. Available Surveys</h3>';

    $surveys = $client->list_surveys($sessionKey);

    echo '<p><strong>Raw API Response:</strong></p>';
    echo '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;">';
    echo htmlspecialchars(json_encode($surveys, JSON_PRETTY_PRINT));
    echo '</pre>';

    if (is_array($surveys) && isset($surveys['status']) && $surveys['status'] === 'No surveys found') {
        echo '<div class="alert alert-warning">';
        echo '<strong>No surveys found!</strong><br>';
        echo 'The API user "' . htmlspecialchars($apiUser) . '" does not have access to any surveys.<br><br>';
        echo '<strong>Possible reasons:</strong><ul>';
        echo '<li>No surveys have been created in LimeSurvey</li>';
        echo '<li>The API user does not have permissions to view surveys</li>';
        echo '<li>The API user is not a superadministrator</li>';
        echo '</ul>';
        echo '<strong>Solution:</strong><br>';
        echo '1. Log in to LimeSurvey as a superadministrator<br>';
        echo '2. Create a survey and activate it<br>';
        echo '3. Or grant the API user "' . htmlspecialchars($apiUser) . '" superadministrator permissions';
        echo '</div>';
    } elseif (is_array($surveys) && !empty($surveys) && isset($surveys[0]['sid'])) {
        echo '<div class="alert alert-success">Found ' . count($surveys) . ' surveys!</div>';

        echo '<table class="table table-bordered">';
        echo '<tr><th>ID</th><th>Title</th><th>Active</th><th>Start Date</th><th>Expires</th><th>Actions</th></tr>';

        foreach ($surveys as $survey) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($survey['sid']) . '</td>';
            echo '<td>' . htmlspecialchars($survey['surveyls_title'] ?? 'N/A') . '</td>';
            echo '<td>' . ($survey['active'] === 'Y' ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>') . '</td>';
            echo '<td>' . htmlspecialchars($survey['startdate'] ?? 'None') . '</td>';
            echo '<td>' . htmlspecialchars($survey['expires'] ?? 'None') . '</td>';
            echo '<td><button class="btn btn-sm btn-primary" onclick="checkParticipants(' . $survey['sid'] . ')">Check Participants</button></td>';
            echo '</tr>';

            // Check participants for active surveys.
            if ($survey['active'] === 'Y') {
                echo '<tr><td colspan="6" id="participants-' . $survey['sid'] . '" style="background: #f9f9f9;"></td></tr>';
            }
        }

        echo '</table>';

        // JavaScript to check participants.
        echo '<script>
        function checkParticipants(sid) {
            var td = document.getElementById("participants-" + sid);
            td.innerHTML = "<p>Loading participants...</p>";

            // Make AJAX call to check participants.
            fetch("' . $CFG->wwwroot . '/blocks/limesurvey/test_api.php?ajax=1&sid=" + sid + "&sesskey=' . sesskey() . '")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        td.innerHTML = "<div class=\"alert alert-danger\">" + data.error + "</div>";
                    } else {
                        var html = "<strong>Participants for survey " + sid + ":</strong><br>";
                        html += "<pre>" + JSON.stringify(data.participants, null, 2) + "</pre>";

                        if (data.user_found) {
                            html += "<div class=\"alert alert-success\">✅ Your email (" + data.user_email + ") was found in this survey!</div>";
                        } else {
                            html += "<div class=\"alert alert-warning\">⚠️ Your email (" + data.user_email + ") was NOT found in this survey.</div>";
                        }

                        td.innerHTML = html;
                    }
                })
                .catch(error => {
                    td.innerHTML = "<div class=\"alert alert-danger\">Error: " + error + "</div>";
                });
        }
        </script>';
    }

    // Check API version (optional - may not work with all LimeSurvey versions).
    echo '<h3>4. LimeSurvey Version</h3>';
    try {
        // Suppress warnings from JSON-RPC client.
        $version = @$client->get_site_settings($sessionKey);

        if (is_array($version) && !empty($version)) {
            echo '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">';
            echo htmlspecialchars(json_encode($version, JSON_PRETTY_PRINT));
            echo '</pre>';
        } else {
            echo '<p class="text-muted">Version information not available (this is optional and doesn\'t affect functionality).</p>';
        }
    } catch (Exception $e) {
        echo '<p class="text-muted">Version information not available: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p class="text-muted">(This is optional and doesn\'t affect the main functionality)</p>';
    }

    // Release session.
    $client->release_session_key($sessionKey);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<strong>Connection Error:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}

echo $OUTPUT->footer();

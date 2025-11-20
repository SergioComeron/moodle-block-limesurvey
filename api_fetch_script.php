<?php
// Incluir el archivo de configuración de Moodle para cargar el entorno
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once 'jsonrpcphp-master/src/org/jsonrpcphp/JsonRPCClient.php';

global $USER, $DB;

// Verificar si el usuario está autenticado
require_login();

$apiUrl = get_config('block_limesurvey', 'api_url');
$apiUser = get_config('block_limesurvey', 'api_user');
$apiPassword = get_config('block_limesurvey', 'api_password');

$content = '';

// **VARIABLES GLOBALES PARA DEBUG** ⬇️
$debug_info = [
    'sessionKey' => null,
    'response' => null,
    'surveyParticipants' => null,
    'tokens' => [],
    'responsesbytoken' => null,
    'hayRespuesta' => null,
    'errors' => []
];
// **FIN VARIABLES DEBUG** ⬆️

// Validar que la configuración esté completa
if (empty($apiUrl) || empty($apiUser) || empty($apiPassword)) {
    echo '<div class="alert alert-warning">Por favor, configure el bloque LimeSurvey en la administración del sitio.</div>';
    exit;
}

// Validar que no sea la URL de ejemplo
if (strpos($apiUrl, 'your-limesurvey-domain') !== false) {
    echo '<div class="alert alert-warning">Por favor, configure la URL real de LimeSurvey en los ajustes del bloque.</div>';
    exit;
}

// Verificar que la configuración de la API esté disponible
if ($apiUrl && $apiUser && $apiPassword) {
    try {
        $lsJSONRPCClient = new \org\jsonrpcphp\JsonRPCClient($apiUrl);
        $sessionKey = $lsJSONRPCClient->get_session_key($apiUser, $apiPassword);
        $debug_info['sessionKey'] = is_string($sessionKey) ? 'OK' : $sessionKey;

        if (is_array($sessionKey)) {
            $content .= '<br>Error obteniendo la clave de sesión: ' . json_encode($sessionKey);
            $debug_info['errors'][] = 'Error en session key: ' . json_encode($sessionKey);
        } else {
            $response = $lsJSONRPCClient->list_surveys($sessionKey);
            $debug_info['response'] = $response;

            if (is_array($response) && !empty($response) && isset($response[0]['sid'])) {
                $content .= '<strong>Encuestas en curso:</strong><ul>';
                $currentDate = time();
                foreach ($response as $survey) {
                    // Validar que $survey sea un array con las claves necesarias
                    if (!is_array($survey) || !isset($survey['active'], $survey['sid'])) {
                        continue;
                    }
                    
                    if ($survey['active'] === 'Y' && !empty($survey['startdate']) && strtotime($survey['startdate']) <= $currentDate && (empty($survey['expires']) || strtotime($survey['expires']) > $currentDate)) {

                        $atributosExtraConfig = get_config('block_limesurvey', 'atributosextra');
                        $atributosExtraArray = array_map('trim', explode(',', $atributosExtraConfig));

                        $surveyParticipants = $lsJSONRPCClient->list_participants(
                            $sessionKey,
                            $survey['sid'],
                            0,
                            5000,
                            false,
                            $atributosExtraArray,
                            ['email' => $USER->email]
                        );
                        $debug_info['surveyParticipants'] = $surveyParticipants;

                        if (is_array($surveyParticipants) && count($surveyParticipants) > 0) {

                            $tokens = [];

                            foreach ($surveyParticipants as $participant) {
                                if (isset($participant['participant_info']['email']) && $participant['participant_info']['email'] === $USER->email) {
                                    $tokens[] = $participant['token'];
                                }
                            }
                            $debug_info['tokens'] = $tokens;

                            if (!empty($tokens)) {
                                $surveyTitle = htmlspecialchars($survey['surveyls_title']);
                                $parsedUrl = parse_url($apiUrl);
                                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
                                $baseUrl = preg_replace('/\/index\.php.*/', '/index.php', $baseUrl);

                                foreach ($tokens as $token) {
                                    $hasResponses = false;

                                    try {
                                        $responsesbytoken = $lsJSONRPCClient->export_responses_by_token(
                                            $sessionKey,
                                            $survey['sid'],
                                            'json',
                                            $token,
                                            null,
                                            'all',
                                            0,
                                            5000
                                        );
                                        $debug_info['responsesbytoken'] = $responsesbytoken;

                                        $hayRespuesta = false;

                                        if (is_array($responsesbytoken)) {
                                            $hayRespuesta = false;
                                        } elseif (is_string($responsesbytoken)) {
                                            $decoded = base64_decode($responsesbytoken, true);
                                            $hayRespuesta = $decoded !== false && $decoded !== '';
                                        } else {
                                            $hayRespuesta = false;
                                        }
                                        $debug_info['hayRespuesta'] = $hayRespuesta;
                                    } catch (Exception $e) {
                                        error_log("Error al exportar respuestas: " . $e->getMessage());
                                        $debug_info['errors'][] = 'Error export_responses: ' . $e->getMessage();
                                        $hasResponses = false;
                                    }

                                    $config_atributosextra = get_config('block_limesurvey', 'atributosextra');
                                    $atributosextra_keys = array_map('trim', explode(',', $config_atributosextra));
                                    $atributosextra = [];
                                    foreach ($atributosextra_keys as $key) {
                                        if (isset($participant[$key]) && !in_array($participant[$key], $atributosextra)) {
                                            $atributosextra[] = htmlspecialchars($participant[$key]);
                                        }
                                    }
                                    $surveyUrl = $baseUrl . '/survey?sid=' . $survey['sid'] . '&token=' . $token;
                                    $extraAttributes = implode(', ', $atributosextra);

                                    $respondedSymbol = $hayRespuesta ? '✅ ' : '⬜️';
                                    $content .= '<li><a href="' . $surveyUrl . '" target="_blank">' . $respondedSymbol . $surveyTitle . ($extraAttributes ? ', ' . $extraAttributes : '') .'</a></li>';
                                }
                            }
                        }
                    }
                }
                $content .= '</ul>';
            } else {
                $content .= '<br>No tienes encuestas en curso.';
            }

            $lsJSONRPCClient->release_session_key($sessionKey);
        }
    } catch (Exception $e) {
        $content .= '<div class="alert alert-danger">Error al conectar con LimeSurvey: ' . htmlspecialchars($e->getMessage()) . '</div>';
        $debug_info['errors'][] = 'Exception: ' . $e->getMessage();
        debugging('LimeSurvey connection error: ' . $e->getMessage(), DEBUG_DEVELOPER);
        echo $content;
        ?>
        <script>
        console.error('=== ERROR LIMESURVEY ===');
        console.error(<?php echo json_encode($debug_info); ?>);
        </script>
        <?php
        exit;
    }
} else {
    $content .= '<br>No se ha configurado correctamente la API de LimeSurvey.';
}

echo $content;
?>
<script>
console.log('=== DEBUG LIMESURVEY ===');
console.log(<?php echo json_encode($debug_info); ?>);
console.log('========================');
</script>

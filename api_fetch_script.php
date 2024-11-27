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

// Verificar que la configuración de la API esté disponible
if ($apiUrl && $apiUser && $apiPassword) {
    $lsJSONRPCClient = new \org\jsonrpcphp\JsonRPCClient($apiUrl);
    $sessionKey = $lsJSONRPCClient->get_session_key($apiUser, $apiPassword);

    if (is_array($sessionKey)) {
        $content .= '<br>Error obteniendo la clave de sesión: ' . json_encode($sessionKey);
    } else {
        $response = $lsJSONRPCClient->list_surveys($sessionKey);

        if (is_array($response)) {
            $content .= '<strong>Encuestas activas:</strong><ul>';
            $currentDate = time();
            // print_r($response);
            foreach ($response as $survey) {
                if ($survey['active'] === 'Y' && !empty($survey['startdate']) && strtotime($survey['startdate']) <= $currentDate && (empty($survey['expires']) || strtotime($survey['expires']) > $currentDate)) {
                    // Obtener los participantes de la encuesta
                    $surveyParticipants = $lsJSONRPCClient->list_participants(
                        $sessionKey, 
                        $survey['sid'], 
                        0, 
                        5000, 
                        false, 
                        false, 
                        ["attribute_1", "attribute_2"]
                    );
                    print_r($surveyParticipants);
                    $filteredParticipants = array_filter($surveyParticipants, function($participant) use ($USER) {
                        return isset($participant['email']) && $participant['email'] === $USER->email;
                    });

                    if (is_array($surveyParticipants) && count($surveyParticipants) > 0) {
                        $tokens = [];

                        foreach ($surveyParticipants as $participant) {
                            // Verificar si 'participant_info' existe y tiene un 'email' que coincide con el email buscado
                            if (isset($participant['participant_info']['email']) && $participant['participant_info']['email'] === $USER->email) {
                                $tokens[] = $participant['token'];
                            }
                        }

                        // Mostrar la encuesta solo si se encontraron tokens
                        if (!empty($tokens)) {
                            $surveyTitle = htmlspecialchars($survey['surveyls_title']);
                            // Parsear la URL y obtener solo la parte base
                            $parsedUrl = parse_url($apiUrl);

                            // Reconstruir la URL sin la ruta adicional
                            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

                            // Remover cualquier parte después de `index.php`
                            $baseUrl = preg_replace('/\/index\.php.*/', '/index.php', $baseUrl);

                            // Mostrar todos los enlaces con los tokens encontrados
                            foreach ($tokens as $token) {
                                // Recuperar el ajuste desde la configuración.
                                $config_atributosextra = get_config('block_limesurvey', 'atributosextra');
                                // print_r ($config_atributosextra);
                                // Convertir la cadena en un array.
                                $atributosextra_keys = array_map('trim', explode(',', $config_atributosextra));
                                // print_r($atributosextra_keys);
                                // Inicializar el array de atributos extra.
                                $atributosextra = [];
                                print_r($participant);
                                // Añadir los valores de los atributos extra al array.
                                foreach ($atributosextra_keys as $key) {
                                    echo $key;
                                    // if (isset($participant['participant_info']['' . $key . ''])) {
                                    echo $participant['participant_info']['attribute_1'];
                                    if (isset($participant['participant_info']['email'])) {
                                        // $atributosextra[] = htmlspecialchars($participant['participant_info'][$key]);
                                        echo "Ha entrado";
                                        $atributosextra[] = htmlspecialchars($participant['participant_info']['attribute_1']);
                                    }
                                }

                                // Agregar valores dinámicos si es necesario.
                                $atributosextra[] = htmlspecialchars($token);


                                // $atritubosextra = ["atributo1", "atributo2", htmlspecialchars($token)];

                                $surveyUrl = $baseUrl . '/survey?sid=' . $survey['sid'] . '&token=' . $token;
                                $extraAttributes = implode(', ', $atributosextra);
                                $content .= '<li><a href="' . $surveyUrl . '" target="_blank">' . $surveyTitle . ($extraAttributes ? ', ' . $extraAttributes : '') .'</a></li>';
                            }
                        }
                    }
                }
            }
            $content .= '</ul>';
        } else {
            $content .= '<br>No tienes encuestas activas.';
        }

        $lsJSONRPCClient->release_session_key($sessionKey);
    }
} else {
    $content .= '<br>No se ha configurado correctamente la API de LimeSurvey.';
}

echo $content;
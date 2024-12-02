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
            $content .= '<strong>Encuestas en curso:</strong><ul>';
            $currentDate = time();
            foreach ($response as $survey) {
                if ($survey['active'] === 'Y' && !empty($survey['startdate']) && strtotime($survey['startdate']) <= $currentDate && (empty($survey['expires']) || strtotime($survey['expires']) > $currentDate)) {

                    // Obtener los atributos extra desde la configuración del bloque.
                    $atributosExtraConfig = get_config('block_limesurvey', 'atributosextra');
                    // Convertir la cadena de atributos separados por comas en un array.
                    $atributosExtraArray = array_map('trim', explode(',', $atributosExtraConfig));

                    // Llamar al método list_participants con los atributos configurados.
                    $surveyParticipants = $lsJSONRPCClient->list_participants(
                        $sessionKey,
                        $survey['sid'],
                        0,
                        5000,
                        false,
                        $atributosExtraArray, // Usar los atributos configurados.
                        ['email' => $USER->email]
                    );

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
                            // echo "<br>";
                            // echo 'el id: '.$survey['sid'];

                            $surveyTitle = htmlspecialchars($survey['surveyls_title']);
                            // Parsear la URL y obtener solo la parte base
                            $parsedUrl = parse_url($apiUrl);

                            // Reconstruir la URL sin la ruta adicional
                            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

                            // Remover cualquier parte después de `index.php`
                            $baseUrl = preg_replace('/\/index\.php.*/', '/index.php', $baseUrl);

                            // Mostrar todos los enlaces con los tokens encontrados
                            foreach ($tokens as $token) {
                               // Inicializa la variable booleana
                                $hasResponses = false;

                                try {
                                    // Llama a la API para exportar las respuestas
                                    $responsesbytoken = $lsJSONRPCClient->export_responses_by_token(
                                        $sessionKey,
                                        $survey['sid'],
                                        'json',
                                        $token,
                                        null,
                                        'all', // Usamos 'all' para incluir respuestas incompletas
                                        0,
                                        5000
                                    );

                                    // Verifica si hay respuestas
                                    $hayRespuesta = false; // Variable para indicar si hay respuesta

                                    if (is_array($responsesbytoken)) {
                                        // Si $respuestas es un array, comprobamos si tiene al menos un elemento no vacío
                                        // $hayRespuesta = !empty($responsesbytoken);
                                        $hayRespuesta = false;
                                        // echo "array";
                                    } elseif (is_string($responsesbytoken)) {
                                        // Si $respuestas es una cadena, podemos comprobar si contiene datos
                                        $decoded = base64_decode($responsesbytoken, true); // El segundo parámetro evita errores al decodificar
                                        $hayRespuesta = $decoded !== false && $decoded !== '';
                                        
                                        // echo "string";
                                    } else {
                                        // Si $respuestas no es ni un array ni una cadena, se considera que no hay respuesta
                                        $hayRespuesta = false;
                                        // echo "nada";
                                    }
                                } catch (Exception $e) {
                                    // Manejo de errores (opcional: puedes dejar la variable en `false` si falla la API)
                                    error_log("Error al exportar respuestas: " . $e->getMessage());
                                    $hasResponses = false;
                                }

                                // Recuperar el ajuste desde la configuración.
                                $config_atributosextra = get_config('block_limesurvey', 'atributosextra');
                                // Convertir la cadena en un array.
                                $atributosextra_keys = array_map('trim', explode(',', $config_atributosextra));
                                // Inicializar el array de atributos extra.
                                $atributosextra = [];
                                // Añadir los valores de los atributos extra al array.
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
} else {
    $content .= '<br>No se ha configurado correctamente la API de LimeSurvey.';
}

echo $content;

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
 * Language strings for block_limesurvey (Spanish).
 *
 * @package   block_limesurvey
 * @copyright 2024, Sergio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activesurveys'] = 'Encuestas activas:';
$string['allcompleted'] = '¡Genial! Has enviado todas las encuestas';
$string['api_password'] = 'Contraseña de API';
$string['api_password_desc'] = 'Contraseña para conectarse a la API de LimeSurvey.';
$string['api_token'] = 'Token de API de LimeSurvey';
$string['api_token_desc'] = 'Ingrese el token de la API de LimeSurvey para habilitar la integración.';
$string['api_url'] = 'URL de la API de LimeSurvey';
$string['api_url_desc'] = 'Ingrese la URL de la API de LimeSurvey (ej., https://su-dominio-limesurvey/index.php/admin/remotecontrol).';
$string['api_user'] = 'Usuario de API';
$string['api_user_desc'] = 'Nombre de usuario para conectarse a la API de LimeSurvey.';
$string['atributosextra'] = 'Atributos extra';
$string['atributosextra_desc'] = 'Opcional. Ingrese números de atributos separados por comas para mostrar como texto adicional debajo de los títulos de las encuestas (ej., "8, 12"). Déjelo vacío si no es necesario. Nota: El formato de título de encuesta funcionará independientemente de esta configuración.';
$string['block_title'] = 'Título personalizado del bloque';
$string['block_title_desc'] = 'Título personalizado para el bloque. Déjelo vacío para usar el nombre por defecto del bloque.';
$string['cachedef_surveys'] = 'Datos de encuestas de LimeSurvey para usuarios';
$string['completed'] = 'Encuesta enviada';
$string['debug_logging'] = 'Activar registro de depuración';
$string['debug_logging_desc'] = 'Activar registro detallado para propósitos de depuración. Cuando está desactivado, solo se registrarán los errores.';
$string['error_config'] = 'Por favor configure el bloque LimeSurvey en la administración del sitio.';
$string['error_config_url'] = 'Por favor configure la URL real de LimeSurvey en la configuración del bloque.';
$string['error_connection'] = 'Error al conectar con LimeSurvey.';
$string['error_loading'] = 'Error al cargar las encuestas.';
$string['error_session'] = 'Error al obtener la clave de sesión: {$a}';
$string['expired'] = 'Expirada';
$string['expiresday'] = 'Expira en 1 día';
$string['expiresin'] = 'Expira en {$a} días';
$string['expirestoday'] = '¡Expira hoy!';
$string['hideresponses'] = 'Ocultar respuestas';
$string['limesurvey:addinstance'] = 'Agregar un nuevo bloque LimeSurvey';
$string['limesurvey:myaddinstance'] = 'Agregar un nuevo bloque LimeSurvey al Panel';
$string['loading'] = 'Cargando encuestas...';
$string['nosurveys'] = 'No tienes encuestas activas.';
$string['pending'] = 'Encuesta pendiente';
$string['pluginname'] = 'Bloque LimeSurvey';
$string['responses'] = 'Respuestas';
$string['surveyprogress'] = 'Progreso de encuestas: {$a->completed} de {$a->total} enviadas';
$string['viewresponses'] = 'Ver respuestas';
$string['survey_title_format'] = 'Formato del título de encuestas';
$string['survey_title_format_desc'] = 'Formato personalizado para los títulos de encuestas usando marcadores de posición. Marcadores disponibles: {title} (título original de la encuesta), {attribute_N} (donde N es el número de atributo, ej., {attribute_9}, {attribute_12}). Déjelo vacío para usar los títulos originales. Ejemplo: "{title} - Curso: {attribute_12} - Profesor: {attribute_9}"';
$string['survey_title_formats_by_id'] = 'Formatos de título por ID de encuesta';
$string['survey_title_formats_by_id_desc'] = 'Formatos de título personalizados para encuestas específicas en formato JSON. Cada ID de encuesta puede tener su propio formato. Si una encuesta no está listada aquí, se usará el "Formato del título de encuestas" general. Ejemplo: {"123": "{title} - Curso: {attribute_12}", "456": "{attribute_9} - {title}", "789": "{title}"}';
$string['startdate'] = 'Inicio';
$string['expiresdate'] = 'Expira';
$string['surveylist'] = 'Lista de encuestas';
$string['completedsurvey_aria'] = 'Encuesta enviada: {$a}';
$string['pendingsurvey_aria'] = 'Encuesta pendiente: {$a}. Se abre en ventana nueva';
$string['progress_aria'] = 'Progreso de la encuesta: {$a} por ciento';
$string['completedstatus'] = 'Enviada';
$string['pendingstatus'] = 'Pendiente';

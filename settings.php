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
 * Block edit form class for the block_limesurvey plugin.
 *
 * @package   block_limesurvey
 * @copyright 2024, Sergio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // // Campo de configuración para el token de la API de LimeSurvey.
    // $settings->add(new admin_setting_configtext(
    //     'block_limesurvey/api_token',
    //     get_string('api_token', 'block_limesurvey'),
    //     get_string('api_token_desc', 'block_limesurvey'),
    //     '', // Valor por defecto (vacío)
    //     PARAM_TEXT
    // ));

    // Campo de configuración para la URL de la API de LimeSurvey.
    $settings->add(new admin_setting_configtext(
        'block_limesurvey/api_url',
        get_string('api_url', 'block_limesurvey'),
        get_string('api_url_desc', 'block_limesurvey'),
        'https://your-limesurvey-domain/index.php/admin/remotecontrol', // URL por defecto
        PARAM_URL
    ));

    // Campo de configuración para el usuario de la API de LimeSurvey.
    $settings->add(new admin_setting_configtext(
        'block_limesurvey/api_user',
        get_string('api_user', 'block_limesurvey'),
        get_string('api_user_desc', 'block_limesurvey'),
        '', // Valor por defecto (vacío)
        PARAM_TEXT
    ));

    // Campo de configuración para la contraseña de la API de LimeSurvey.
    $settings->add(new admin_setting_configpasswordunmask(
        'block_limesurvey/api_password',
        get_string('api_password', 'block_limesurvey'),
        get_string('api_password_desc', 'block_limesurvey'),
        '' // Valor por defecto (vacío)
    ));

    $settings->add(new admin_setting_configtext(
        'block_limesurvey/atributosextra', // Nombre interno del ajuste
        get_string('atributosextra', 'block_limesurvey'), // Nombre que verá el usuario
        get_string('atributosextra_desc', 'block_limesurvey'), // Descripción del ajuste
        '', // Valor predeterminado (puedes poner "atributo1,atributo2" si lo prefieres)
        PARAM_TEXT // Tipo de dato (texto)
    ));

    // Campo de configuración para habilitar debug logging.
    $settings->add(new admin_setting_configcheckbox(
        'block_limesurvey/debug_logging',
        get_string('debug_logging', 'block_limesurvey'),
        get_string('debug_logging_desc', 'block_limesurvey'),
        0 // Valor por defecto: desactivado
    ));

    // Campo de configuración para el nombre personalizado del bloque.
    $settings->add(new admin_setting_configtext(
        'block_limesurvey/block_title',
        get_string('block_title', 'block_limesurvey'),
        get_string('block_title_desc', 'block_limesurvey'),
        '', // Valor por defecto: vacío (usa el nombre por defecto)
        PARAM_TEXT
    ));
}
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
     // Campo de configuración para el token de la API de LimeSurvey.
     $settings->add(new admin_setting_configtext(
         'block_limesurvey/api_token',
         get_string('api_token', 'block_limesurvey'),
         get_string('api_token_desc', 'block_limesurvey'),
         '', // Valor por defecto (vacío)
         PARAM_TEXT
     ));
 
     // Campo de configuración para la URL de la API de LimeSurvey.
     $settings->add(new admin_setting_configtext(
         'block_limesurvey/api_url',
         get_string('api_url', 'block_limesurvey'),
         get_string('api_url_desc', 'block_limesurvey'),
         'https://your-limesurvey-domain/index.php/admin/remotecontrol', // URL por defecto
         PARAM_URL
     ));
 }
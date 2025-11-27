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
 * Block limesurvey main class.
 *
 * @package   block_limesurvey
 * @copyright 2024, Sergio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * LimeSurvey block class.
 */
class block_limesurvey extends block_base {

    /**
     * Initialize block.
     */
    public function init() {
        // Get custom block title from config, or use default.
        $customtitle = get_config('block_limesurvey', 'block_title');
        if (!empty($customtitle)) {
            $this->title = format_string($customtitle);
        } else {
            $this->title = get_string('pluginname', 'block_limesurvey');
        }
    }

    /**
     * Get block content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '<div id="limesurvey-content">' .
            get_string('loading', 'block_limesurvey') . '</div>';
        $this->content->footer = '';

        // Initialize AMD module to load surveys.
        $PAGE->requires->js_call_amd('block_limesurvey/surveys', 'init');

        return $this->content;
    }

    /**
     * Define where this block can be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

    /**
     * Allow multiple instances of this block.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * This block has global configuration.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
}
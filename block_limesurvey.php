<?php
class block_limesurvey extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_limesurvey');
    }

    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        // Inicializar el contenido del bloque
        $this->content = new stdClass();
        $this->content->text = 'Hola, ' . $USER->username . '!';
        $apitoken = get_config('block_limesurvey', 'api_token');
        $apiurl = get_config('block_limesurvey', 'api_url');
        $this->content->text .= '<br>API Token: ' . $apitoken;
        $this->content->text .= '<br>API URL: ' . $apiurl;
        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

    function has_config() {
        return true;
    }
}
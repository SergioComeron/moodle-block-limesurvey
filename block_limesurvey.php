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

        return $this->content;
    }
}
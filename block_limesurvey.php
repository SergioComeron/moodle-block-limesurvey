<?php
require_once 'jsonrpcphp-master/src/org/jsonrpcphp/JsonRPCClient.php';

class block_limesurvey extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_limesurvey');
    }

    public function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        // Inicializar el contenido del bloque con un mensaje de carga
        $this->content = new stdClass();
        $this->content->text = '<div id="limesurvey-content">Cargando encuestas...</div>';
        
        $this->content->footer = '<script async defer>
            document.addEventListener("DOMContentLoaded", function() {
                fetch("'.$CFG->wwwroot. '/blocks/limesurvey/api_fetch_script.php")
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById("limesurvey-content").innerHTML = data;
                    })
                    .catch(error => {
                        document.getElementById("limesurvey-content").innerHTML = "Error cargando encuestas.";
                        console.error("Error:", error);
                    });
            });
        </script>';
        $this->content->footer = '<div style="font-size: small;">
        ✅ Encuesta realizada.<br>
        ⬜️ Encuesta pendiente.
    </div>' . $this->content->footer;
        return $this->content;
    }

    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

    public function has_config() {
        return true;
    }
}
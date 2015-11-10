<?php

namespace OCP;

class Template {

    private $template;
    private $app;
    private $vars;

    public function __construct($app, $template){
        $this->vars = array();
        $this->$app = $app;
        $this->$template = $template;
    }

    public function fetchPage() {
        return '<html></html>';
    }

    public function assign($key, $value) {
        $this->vars[$key] = $value;
        return true;
    }
}

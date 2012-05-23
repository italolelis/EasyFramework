<?php

interface ITemplateEngine {

    public function display($layout, $view, $ext = "tpl");

    public function set($var, $value);
}

<?php

interface ITemplateEngine {

    public function display($view, $ext = "tpl");

    public function set($var, $value);
}

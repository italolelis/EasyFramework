<?php

namespace Easy\View\Engine;

interface ITemplateEngine
{

    public function display($layout, $view, $ext = "tpl");

    public function set($var, $value);
}

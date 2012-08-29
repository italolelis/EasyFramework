<?php

namespace Easy\View\Controls;

class SelectItem
{

    private $value;
    private $display;

    public function __construct($display, $value)
    {
        $this->display = $display;
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
    }

}
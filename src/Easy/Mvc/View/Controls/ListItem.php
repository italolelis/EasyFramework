<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Controls;

class ListItem
{

    private $value;
    private $display;

    /**
     * Initializes a new instance of the SelectListItem class.
     * @param string $display
     * @param string $value
     */
    public function __construct($display, $value)
    {
        $this->display = $display;
        $this->value = $value;
    }

    /**
     * Gets the value of the selected item.
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the selected item.
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the text of the selected item.
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Sets the text of the selected item.
     * @param string $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

}
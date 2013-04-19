<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine;

interface EngineInterface
{

    /**
     * Display a view
     * @param string $name The view's name
     * @param string $layout The layout to use
     * @param bool $output Will the view bem outputed?
     */
    public function render($name, $layout, $output = true);

    /**
     * Display a view
     * @param string $name The view's name
     * @param string $layout The layout to use
     * @param bool $output Will the view bem outputed?
     */
    public function renderResponse($name, $layout);

    /**
     * Sets var to be used in the view
     * @param string $var The key name
     * @param mixed $value The value
     */
    public function set($var, $value);

    /**
     * Gets the engine options
     */
    public function getOptions();
}
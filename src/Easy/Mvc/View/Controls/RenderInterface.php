<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Controls;

interface RenderInterface
{

    public function render($selected, $defaultText = null);
}
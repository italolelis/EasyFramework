<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Builders;

abstract class TagRenderMode
{

    const NORMAL = 0;
    const START_TAG = 1;
    const END_TAG = 2;
    const SELF_CLOSING = 3;

}
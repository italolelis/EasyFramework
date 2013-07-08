<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security;

/**
 * Represents the hash interface
 */
interface HashInterface
{

    public function hash($string);

    public function check($string, $hash);
}

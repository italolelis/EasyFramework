<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Annotation;

/**
 * ConfigurationInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ConfigurationInterface
{

    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    function getAliasName();

    /**
     * Returns whether multiple annotations of this type are allowed
     *
     * @return Boolean
     */
    function allowArray();
}
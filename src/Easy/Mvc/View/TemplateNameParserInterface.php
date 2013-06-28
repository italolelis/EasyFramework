<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View;

/**
 * TemplateNameParserInterface converts template names to TemplateReferenceInterface
 * instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface TemplateNameParserInterface
{

    /**
     * Convert a template name to a TemplateReferenceInterface instance.
     *
     * @param string $name A template name
     *
     * @return TemplateReferenceInterface A template
     *
     * @api
     */
    public function parse($name);
}

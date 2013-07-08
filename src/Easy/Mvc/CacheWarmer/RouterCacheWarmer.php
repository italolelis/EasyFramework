<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\CacheClearer;

/**
 * Interface for finding all the templates.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
interface TemplateFinderInterface
{

    /**
     * Find all the templates.
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    public function findAllTemplates();
}
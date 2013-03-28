<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Controller\Metadata;

use Easy\Annotations\AnnotationManager;

class ControllerMetadata
{

    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getLayout($action)
    {
        $manager = new AnnotationManager("Layout", $this->class);
        $annotation = $manager->getMethodAnnotation($action);
        
        if ($annotation !== false) {
            return $annotation->value;
        } else {
            return null;
        }
    }

}
<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

/**
 * ControllerAwareInterface should be implemented by classes that depends on a Controller.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface ControllerAwareInterface
{

    /**
     * Sets the Controller.
     *
     * @param Controller $controller A Controller instance
     */
    public function setController(Controller $controller = null);

    /**
     * Gets the Controller.
     * @return Controller The Controller instance
     */
    public function getController();
}
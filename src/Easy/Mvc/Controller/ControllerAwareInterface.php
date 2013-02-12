<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param ControllerInterface $controller A ControllerInterface instance
     */
    public function setController(ControllerInterface $controller = null);

    /**
     * Gets the Controller.
     * @return ControllerInterface The ControllerInterface instance
     */
    public function getController();
}
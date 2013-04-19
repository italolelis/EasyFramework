<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Controller;

use Easy\Network\Request;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

interface ControllerResolverInterface
{

    /**
     * Returns the Controller instance associated with a Request.
     * As several resolvers can exist for a single application, a resolver must
     * return false when it is not able to determine the controller.
     * The resolver must only throw an exception when it should be able to load
     * controller but cannot because of some errors made by the developer.
     * @param Request $request
     * @return mixed|boolean A PHP callable representing the Controller, or false if this resolver is not able to determine the controller
     * @throws InvalidArgumentException If the controller can't be found
     */
    public function getController(Request $request);
}

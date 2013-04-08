<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\Dbal;

interface ISchema
{

    public function listTables();

    /**
     * @return IDriver The driver object for this schema
     */
    public function getDriver();
}

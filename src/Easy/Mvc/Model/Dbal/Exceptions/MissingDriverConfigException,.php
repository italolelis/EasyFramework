<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\Dbal\Exceptions;

/**
 * Exception class to be thrown when a driver configuration is not found
 * 
 * @since 1.6
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class MissingDriverConfigException extends DbalException
{

    protected $_messageTemplate = 'The datasource configuration "%s" was not found in database.yml';

}

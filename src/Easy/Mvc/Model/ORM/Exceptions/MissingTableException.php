<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM\Exceptions;

/**
 * Exception class to be thrown when a schema is not found
 * 
 * @since 1.6
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class MissingSchemaException extends OrmException
{

    protected $_messageTemplate = 'Table %s for model %s was not found in datasource %s.';

}

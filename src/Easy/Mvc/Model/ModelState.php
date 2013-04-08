<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model;

use Easy\Core\Object;
use Symfony\Component\Validator\Validation;

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key
 */
class ModelState extends Object
{

    private $validator;
    private $errors;

    public function __construct()
    {
        $this->validator = Validation::createValidatorBuilder()
                ->enableAnnotationMapping()
                ->getValidator();
    }

    public function validate(IModel $model)
    {
        $this->errors = $this->validator->validate($model);
    }

    public function isValid()
    {
        return empty($this->errors) ? false : true;
    }

}


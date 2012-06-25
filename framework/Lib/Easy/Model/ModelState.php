<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @since         EasyFramework v 0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Validation', 'Utility');
App::uses('Object', 'Core');

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 *
 * @package Easy.Model
 */
class ModelState extends Object
{

    protected $data;
    protected $validate;

    /**
     * List of validation errors.
     *
     * @var array
     */
    protected $validationErrors = array();
    protected $validationClass = 'Validation';

    /**
     * Name of the validation string domain to use when translating validation errors.
     *
     * @var string
     */
    protected $validationDomain = null;

    public function ModelState($data, $validate)
    {
        $this->data = $data;
        $this->validate = $validate;
    }

    public function getValidationDomain()
    {
        if (empty($this->validationDomain)) {
            $this->validationDomain = 'default';
        }
        return $this->validationDomain;
    }

    public function isValid()
    {
        return $this->validate($this->data);
    }

    public function validate(array $data)
    {
        $validationDomain = $this->getValidationDomain();

        $methods = array_map('strtolower', get_class_methods($this));

        foreach ($this->validate as $fieldName => $ruleSet) {
            if (!is_array($ruleSet) || (is_array($ruleSet) && isset($ruleSet['rule']))) {
                $ruleSet = array($ruleSet);
            }
            $default = array(
                'allowEmpty' => null,
                'required' => null,
                'rule' => 'blank',
                'last' => true,
                'on' => null
            );

            foreach ($ruleSet as $index => $validator) {

                $validator = array_merge($default, $validator);

                if (is_array($validator['rule'])) {
                    $rule = $validator['rule'][0];
                    unset($validator['rule'][0]);
                    $ruleParams = array_merge(array($data[$fieldName]), array_values($validator['rule']));
                } else {
                    $rule = $validator['rule'];
                    $ruleParams = array($data[$fieldName]);
                }

                $valid = true;

                if (substr($rule, 0, 1) === "!") {
                    $rule = str_replace("!", "", $rule);
                    if (method_exists($this->validationClass, $rule)) {
                        $valid = !call_user_func_array(array($this->validationClass, $rule), $ruleParams);
                    }
                } else {
                    if (in_array(strtolower($rule), $methods)) {
                        $ruleParams[] = $validator;
                        $ruleParams[0] = array($fieldName => $ruleParams[0]);
                        $valid = $this->dispatchMethod($rule, $ruleParams);
                    } elseif (method_exists($this->validationClass, $rule)) {
                        $valid = call_user_func_array(array($this->validationClass, $rule), $ruleParams);
                    } elseif (!is_array($validator['rule'])) {
                        $valid = preg_match($rule, $data[$fieldName]);
                    }
                }

                if (!$valid) {
                    if (is_string($valid)) {
                        $message = $valid;
                    } elseif (isset($validator['message'])) {
                        $args = null;
                        if (is_array($validator['message'])) {
                            $message = $validator['message'][0];
                            $args = array_slice($validator['message'], 1);
                        } else {
                            $message = $validator['message'];
                        }
                        if (is_array($validator['rule']) && $args === null) {
                            $args = array_slice($ruleSet[$index]['rule'], 1);
                        }
                        $message = $message = __d($validationDomain, $message, $args);
                    }
                    $this->invalidate($fieldName, $message);
                }
            }
        }
        return $this->validationErrors;
    }

    /**
     * Marks a field as invalid, optionally setting the name of validation
     * rule (in case of multiple validation for field) that was broken.
     *
     * @param string $field The name of the field to invalidate
     * @param mixed $value Name of validation rule that was not failed, or validation message to
     *    be returned. If no validation key is provided, defaults to true.
     * @return void
     */
    public function invalidate($field, $value = true)
    {
        if (!is_array($this->validationErrors)) {
            $this->validationErrors = array();
        }
        $this->validationErrors[$field] = $value;
    }

}


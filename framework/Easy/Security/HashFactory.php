<?php

/**
 * Core Security
 *
 * PHP 5
 *
 * FROM CAKEPHP
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Utility
 * @since         CakePHP(tm) v .0.10.0.1233
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Security;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Utility\Inflector;

/**
 * Security Library contains utility methods related to security
 *
 * @package Easy.Security
 */
class HashFactory
{

    public function build($type = null)
    {
        $options = array();

        if ($type === null) {
            $type = Inflector::camelize(Config::read("Security.hash"));
            if (is_array($type)) {
                $options = array_values($type);
                $type = key($type);
            }
        }

        $className = App::classname($type, "Security/Hash");
        return new $className($options);
    }

}

<?php

/**
 * Cookie Component
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Controller.Component
 * @since         CakePHP(tm) v 1.2.0.4213
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Cookies', 'Storage');

/**
 * Cookie Component.
 *
 * Cookie handling for the controller.
 *
 * @package       Cake.Controller.Component
 * @link http://book.cakephp.org/2.0/en/core-libraries/components/cookie.html
 *
 */
class CookieComponent extends Component {

    public static function delete($name) {
        Cookie::read($name);
    }

    public static function read($name) {
        Cookie::read($name);
    }

    public static function write($name, $value, $expires = null) {
        Cookie::write($name, $value);
    }

    public static function encrypt($value) {
        Cookie::encrypt($value);
    }

    public static function decrypt($value) {
        Cookie::decrypt($value);
    }

}

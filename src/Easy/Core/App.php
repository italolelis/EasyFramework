<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Core;

/**
 * App is responsible for path management, class location and class loading.
 * 
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class App
{

    /**
     * Obtêm a versão do core
     * @return string 
     */
    public static function getVersion()
    {
        return "2.0.0-rc";
    }

    /**
     * Return the classname namespaced. This method check if the class is defined on the
     * application/plugin, otherwise try to load from the CakePHP core
     *
     * @param string $class Classname
     * @param string $type Type of class
     * @param string $suffix Classname suffix
     * @return boolean|string False if the class is not found or namespaced classname
     */
    public static function classname($class, $type = '', $suffix = '')
    {
        if (strpos($class, '\\') !== false) {
            return $class;
        }

        $name = $class;

        $checkCore = true;

        $base = Config::read('App.namespace');
        if ($base === null) {
            $base = "App";
        }
        $base = rtrim($base, '\\');

        if ($type === 'Lib') {
            $fullname = '\\' . $name . $suffix;
            if (class_exists($base . $fullname)) {
                return $base . $fullname;
            }
        }
        $fullname = '\\' . str_replace('/', '\\', $type) . '\\' . $name . $suffix;

        if (class_exists($base . $fullname)) {
            return $base . $fullname;
        }

        if ($checkCore) {
            if ($type === 'Lib') {
                $fullname = '\\' . $name . $suffix;
            }
            if (class_exists('Easy' . $fullname)) {
                return 'Easy' . $fullname;
            }
        }
        return false;
    }

}

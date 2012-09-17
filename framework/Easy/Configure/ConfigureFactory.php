<?php

/**
 * 
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 2.0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Configure;

use Easy\Core\App;

class ConfigureFactory
{

    public function build($type)
    {
        $class = App::classname($type, 'Configure/Engines', 'Reader');
        return new $class(App::path('Config'));
    }

}
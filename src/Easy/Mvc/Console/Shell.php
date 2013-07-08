<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Console;

use Symfony\Component\Console\Shell as BaseShell;

/**
 * Shell.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Shell extends BaseShell
{

    /**
     * Returns the shell header.
     *
     * @return string The header string
     */
    protected function getHeader()
    {
        return <<<EOF
<info>
      _____                  __                  ___
     / ____|                / _|                |__ \
    | (___  _   _ _ __ ___ | |_ ___  _ __  _   _   ) |
     \___ \| | | | '_ ` _ \|  _/ _ \| '_ \| | | | / /
     ____) | |_| | | | | | | || (_) | | | | |_| |/ /_
    |_____/ \__, |_| |_| |_|_| \___/|_| |_|\__, |____|
             __/ |                          __/ |
            |___/                          |___/

</info>
EOF
                . parent::getHeader();
    }

}

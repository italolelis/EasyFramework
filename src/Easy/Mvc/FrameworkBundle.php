<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc;

use Easy\HttpKernel\Bundle\Bundle;
use Easy\Mvc\DependencyInjection\Compiler\RegisterKernelListenersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;

class FrameworkBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container->addScope(new Scope('request'));

        $container->addCompilerPass(new RegisterKernelListenersPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

}
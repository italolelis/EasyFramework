<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

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
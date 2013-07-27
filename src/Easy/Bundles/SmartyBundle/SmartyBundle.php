<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SmartyBundle;

use Easy\Bundles\SmartyBundle\DependencyInjection\Compiler\RegisterExtensionsPass;
use Easy\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SmartyBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterExtensionsPass());
    }

}
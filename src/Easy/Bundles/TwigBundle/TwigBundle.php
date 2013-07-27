<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\TwigBundle;

use Easy\Bundles\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Easy\Bundles\TwigBundle\DependencyInjection\Compiler\TwigLoaderPass;
use Easy\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        //$container->addCompilerPass(new ExtensionPass());
        $container->addCompilerPass(new TwigEnvironmentPass());
        $container->addCompilerPass(new TwigLoaderPass());
        //$container->addCompilerPass(new ExceptionListenerPass());
    }

}
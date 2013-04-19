<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Bundles\MonologBundle;

use Easy\Bundles\MonologBundle\DependencyInjection\Compiler\AddProcessorsPass;
use Easy\Bundles\MonologBundle\DependencyInjection\Compiler\DebugHandlerPass;
use Easy\Bundles\MonologBundle\DependencyInjection\Compiler\LoggerChannelPass;
use Easy\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Bundle.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class MonologBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass($channelPass = new LoggerChannelPass());
        $container->addCompilerPass(new DebugHandlerPass($channelPass));
        $container->addCompilerPass(new AddProcessorsPass());
    }

}

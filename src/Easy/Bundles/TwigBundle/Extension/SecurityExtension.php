<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Bundles\TwigBundle\Extension;

use Easy\Security\Authentication\AuthenticationInterface;
use Easy\Security\Identity\PrincipalInterface;
use Twig_Extension;
use Twig_Filter_Method;

/**
 * Twig extension for Easy assets helper
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SecurityExtension extends Twig_Extension
{

    private $auth;

    /**
     *
     * @var PrincipalInterface
     */
    private $user;

    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
        $this->user = $this->auth->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'is_granted' => new \Twig_Function_Method($this, 'isGranted'),
        );
    }

    public function isGranted($role, $object = null)
    {
        if ($object === null) {
            $object = $this->user;
        }

        if ($object->isInRole($role)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'security';
    }

}

<?php

namespace Easy\Bundles\SmartyBundle\Extension;

use Easy\Bundles\SmartyBundle\Extension\Plugin\ModifierPlugin;
use Easy\Security\Authentication\AuthenticationInterface;

/**
 * SecurityExtension exposes security context features.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class SecurityExtension extends AbstractExtension
{

    protected $context;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface $context A SecurityContext instance
     */
    public function __construct(AuthenticationInterface $context = null)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlugins()
    {
        return array(
            new ModifierPlugin('is_granted', $this, 'isGranted'),
        );
    }

    public function isGranted($role, $object = null)
    {
        if (null === $object) {
            $object = $this->context->getUser();
        }

        return $object->isInRole($role);
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
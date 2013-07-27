<?php

namespace Easy\Bundles\SmartyBundle\Extension;

use Easy\Bundles\SmartyBundle\Extension\Plugin\BlockPlugin;
use Easy\Bundles\SmartyBundle\Extension\Plugin\ModifierPlugin;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides integration of the Translation component with Smarty[Bundle].
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class TranslationExtension extends AbstractExtension
{

    /**
     * {@inheritdoc}
     */
    public function getPlugins()
    {
        return array(
            new BlockPlugin('trans', $this, 'transBlock'),
            new ModifierPlugin('trans', $this, 'transModifier')
        );
    }

    /**
     * Block plugin for 'trans'.
     *
     * @see TranslatorInterface::trans()
     *
     * @param array $params  Parameters to pass to the translator
     * @param string $message Message to translate
     */
    public function transBlock(array $params = array(), $message = null, \Smarty_Internal_Template $template, &$repeat)
    {
        // only output on the closing tag
        if (!$repeat && isset($message)) {
            return __($message, $params);
        }
    }

    /**
     * Modifier plugin for 'trans'.
     *
     * @see TranslatorInterface::trans()
     *
     * Usage in template context:
     * <code>
     * {"text to be translated"|trans}
     * </code>
     */
    public function transModifier($message, $parameters = array())
    {
        return __($message, $parameters);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'translator';
    }

}

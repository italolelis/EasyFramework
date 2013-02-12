<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\ControllerAware;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Symfony\Component\Translation\Translator as SfTranslator;

class Translator extends ControllerAware
{

    /**
     * @var SfTranslator 
     */
    private $translator;

    /**
     * @var Locale 
     */
    private $locale;

    /**
     * @var string 
     */
    public $fallback;

    public function initialize(InitializeEvent $event)
    {
        $this->translator = new SfTranslator($this->locale->getLocale());
        $this->setFallbackLocale($this->fallback);
    }

    /**
     * Gets the Locale object
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the Locale object
     * @param \Easy\Mvc\Controller\Component\Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Sets the fallback locale name
     * @param string $locale The new fallback locale using pattern locale_Country
     */
    public function setFallbackLocale($locale)
    {
        $this->translator->setFallbackLocale($locale);
    }

    public function trans($value)
    {
        return $this->translator->trans($value);
    }

    public function addResource($format, $resource, $locale, $domain = "messges")
    {
        return $this->translator->addResource($format, $resource, $locale, $domain);
    }

    public function transChoice($id, $number, array $parameters = array(), $domain = "messges", $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

}
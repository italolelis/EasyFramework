<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller\Component;

use Easy\Localization\I18n;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Locale\Locale as BaseLocale;

class Locale
{

    /**
     * @var string 
     */
    private $locale;

    /**
     * @var SessionInterface 
     */
    private $session;

    /**
     * @var string 
     */
    private $timezone;

    public function configLocale()
    {
        $language = strtolower(str_replace("_", "-", $this->locale));
        $catalog = I18n::getInstance()->l10n->catalog($language);
        setlocale(LC_ALL, $catalog['locale'] . "." . $catalog['charset'], "ptb");
        date_default_timezone_set($this->timezone);
    }

    /**
     * Gets the Session object
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Sets the Session object
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Gets the current locale based on session or http
     * @return string The locale name
     */
    public function getLocale()
    {
        if (!$this->locale) {
            $locale = $this->session->getLocale();
            if (!$locale) {
                $locale = BaseLocale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
            BaseLocale::setDefault($locale);
            $this->locale = $locale;
        }
        return $this->locale;
    }

    /**
     * Sets the current locale
     * @param string $locale The new locale using pattern locale_Country
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Gets the timezone string
     * @return string The internatinal timezone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Sets the timezone string
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

}
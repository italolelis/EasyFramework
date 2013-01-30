<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Localization\I18n;
use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Symfony\Component\Locale\Locale as sfLocale;

class Locale extends Component
{

    /**
     * @var string 
     */
    private $locale;

    /**
     * @var Session 
     */
    private $session;

    /**
     * @var string 
     */
    private $timezone;

    public function initialize(InitializeEvent $event)
    {
        $this->controller = $event->getController();
        $this->configLocale();
    }

    private function configLocale()
    {
        $language = strtolower(str_replace("_", "-", $this->locale));
        $catalog = I18n::getInstance()->l10n->catalog($language);
        setlocale(LC_ALL, $catalog['locale'] . "." . $catalog['charset'], "ptb");
        date_default_timezone_set($this->timezone);
    }

    /**
     * Gets the Session object
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Sets the Session object
     * @param \Easy\Mvc\Controller\Component\Session $session
     */
    public function setSession(Session $session)
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
                $locale = sfLocale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
            sfLocale::setDefault($locale);
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
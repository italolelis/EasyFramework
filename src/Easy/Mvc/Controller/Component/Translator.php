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
use Symfony\Component\Locale\Locale;
use Symfony\Component\Translation\Translator as SfTranslator;

class Translator extends Component
{

    /**
     * @var SfTranslator 
     */
    private $translator;

    /**
     * @var Session 
     */
    private $session;

    /**
     * @var string 
     */
    public $fallback;

    public function initialize(InitializeEvent $event)
    {
        $this->controller = $event->getController();
        //$this->translator = new SfTranslator($this->getDefaultLocale());
        //$this->setFallbackLocale($this->fallback);
        $this->configl18NLib();
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function configl18NLib()
    {
        $language = I18n::loadLanguage();
        if (!$language) {
            $language = strtolower(str_replace("_", "-", $this->fallback));
        }
        $catalog = I18n::getInstance()->l10n->catalog($language);
        setlocale(LC_ALL, $catalog['locale'] . "." . $catalog['charset'], 'ptb');
    }

    public function getDefaultLocale()
    {
        $locale = $this->session->getLocale();

        if (!$locale) {
            $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
        Locale::setDefault($locale);
        return $locale;
    }

    public function setFallbackLocale($locale)
    {
        $this->translator->setFallbackLocale($locale);
    }

    public function getLocale()
    {
        return $this->translator->getLocale();
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
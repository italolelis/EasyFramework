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

use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Symfony\Component\Translation\Translator as SfTranslator;

class Translator extends Component
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

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

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
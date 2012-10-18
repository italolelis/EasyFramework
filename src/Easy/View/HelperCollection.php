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

namespace Easy\View;

use Easy\Collections\Generic\ObjectCollection;
use Easy\Controller\Controller;
use Easy\Core\App;
use Easy\Utility\Inflector;
use Easy\View\Exception\MissingHelperException;
use Easy\View\View;

class HelperCollection extends ObjectCollection
{

    /**
     * @var View View object to use when making helpers. 
     */
    protected $view;

    /**
     * Constructor
     * @param $view View       	
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }

    public function init(Controller $controller)
    {
        if (empty($controller->helpers)) {
            return;
        }
        foreach ($controller->helpers as $name) {
            $this->load($name);
        }
    }

    /**
     * Loads/constructs a helper.
     * Will return the instance in the registry if it already exists.
     * By setting `$enable` to false you can disable callbacks for a helper. Alternatively you
     * can set `$settings['enabled'] = false` to disable callbacks. This alias is provided so that
     * when
     * declaring $helpers arrays you can disable callbacks on helpers.
     *
     * You can alias your helper as an existing helper by setting the 'className' key, i.e.,
     * {{{
     * public $helpers = array(
     * 'Html' => array(
     * 'className' => 'AliasedHtml'
     * );
     * );
     * }}}
     * All calls to the `Html` helper would use `AliasedHtml` instead.
     *
     * @param $helper string
     *       	 Helper name to load
     * @param $settings array
     *       	 Settings for the helper.
     * @return Helper A helper object, Either the existing loaded helper or a new one.
     * @throws MissingHelperException when the helper could not be found
     */
    public function load($helper, $settings = array())
    {
        $class = Inflector::camelize($helper);
        $helperClass = App::classname($class, 'View\Helper', 'Helper');

        if (!class_exists($helperClass)) {
            $this->Add($helper, new $helperClass($this));
            $helperClass = $this->offsetGet($helper);
            $this->view->set($helper, $helperClass);

            return $helperClass;
        } elseif (class_exists($helperClass)) {
            if (!$this->ContainsKey($helper)) {
                $this->Add($helper, new $helperClass($this));
                $helperClass = $this->offsetGet($helper);
                $this->view->set($helper, $helperClass);
            }
            return $this->offsetGet($helper);
        } else {
            throw new MissingHelperException(__('Helper class %s could not be found.', $helper));
        }
    }

}
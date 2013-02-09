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

namespace Easy\Mvc\View\Helper;

use Easy\Core\Config;
use Easy\Mvc\Controller\ControllerInterface;
use Easy\Mvc\View\Builders\ButtonBuilder;
use Easy\Mvc\View\Builders\HtmlButtonType;
use Easy\Mvc\View\Builders\TagBuilder;
use Easy\Mvc\View\Builders\TagRenderMode;
use Easy\Mvc\View\Helper;
use Easy\Utility\Hash;

class HtmlHelper extends Helper
{

    public $scriptsForLayout = null;
    public $stylesForLayout = null;

    /**
     * The Url Helper Object
     * @var UrlHelper 
     */
    public $url;

    public function __construct(ControllerInterface $controller, UrlHelper $url)
    {
        parent::__construct($controller);
        $this->url = $url;
    }

    public function getScriptsForLayout()
    {
        return $this->scriptsForLayout;
    }

    public function setScriptsForLayout($scriptsForLayout)
    {
        $this->scriptsForLayout = $scriptsForLayout;
    }

    public function getStylesForLayout()
    {
        return $this->stylesForLayout;
    }

    public function setStylesForLayout($stylesForLayout)
    {
        $this->stylesForLayout = $stylesForLayout;
    }

    public function tag($tag, $content = '', $attr = null, $mode = TagRenderMode::NORMAL)
    {
        $tag = new TagBuilder($tag);
        $tag->mergeAttributes($attr);
        $tag->setInnerHtml($content);
        return $tag->toString($mode);
    }

    public function div($class, $content, $type)
    {
        $attr = array(
            'class' => 'input ' . $type
        );

        if (is_array($class)) {
            $attr = $class + $attr;
        } elseif (is_string($class)) {
            $attr['class'] .= ' ' . $class;
        }

        return $this->tag('div', $content, $attr);
    }

    public function span($message, array $attributes = array())
    {
        if (!empty($message)) {
            $message = "<br/>" . $this->tag('span', $message, $attributes);
        } else {
            $message = "";
        }
        return $message;
    }

    public function actionLink($text, $action, $controller = null, $params = null, $area = true, $attr = array())
    {
        $attr['href'] = $this->url->action($action, $controller, $params, $area);
        return $this->tag('a', $text, $attr);
    }

    public function link($text, $url = null, $attr = array(), $full = true)
    {
        if (is_null($url)) {
            $url = $text;
        }
        if (!isset($attr['href'])) {
            $attr['href'] = $this->url->content($url, $full);
        }

        return $this->tag('a', $text, $attr);
    }

    public function image($src, $attr = array())
    {
        $attr += array(
            'alt' => '',
            'title' => array_key_exists('alt', $attr) ? $attr['alt'] : ''
        );

        $attr['src'] = $src;

        return $this->tag('img', null, $attr, true);
    }

    public function imagelink($src, $url, $img_attr = array(), $attr = array(), $full = false)
    {
        $image = $this->image($src, $img_attr);
        return $this->link($image, $url, $attr, $full);
    }

    public function meta($meta)
    {
        if (!is_array($meta)) {
            $meta = array($meta);
        }

        $attr = array(
            'name' => null,
            'content' => null
        );
        $output = "";
        foreach ($meta as $name => $content) {
            $attr['name'] = $name;
            $attr['content'] = $content;
            $output .= $this->tag('meta', null, $attr, TagRenderMode::SELF_CLOSING);
        }

        return $output;
    }

    public function stylesheet($href, $inline = true, $attr = array())
    {
        if (!is_array($href)) {
            $href = array($href);
        }
        $default = Hash::merge(array(
                    'href' => "",
                    'rel' => 'stylesheet',
                    'type' => 'text/css',
                    'version' => false
                        ), $attr);
        $output = '';
        $version = "";
        if ($default['version']) {
            $version = "?v=" . Hash::arrayUnset($default, 'version');
        }

        foreach ($href as $tag) {
            $attr = Hash::merge($default, array(
                        'href' => $this->url->content($tag) . $version,
            ));
            $output .= $this->tag('link', null, $attr, TagRenderMode::SELF_CLOSING);
        }

        if ($inline) {
            return $output;
        } else {
            $this->stylesForLayout .= $output;
        }
    }

    public function script($src, $inline = true, $attr = array())
    {
        if (!is_array($src)) {
            $src = array($src);
        }
        $default = Hash::merge(array(
                    'src' => "",
                    'version' => false
                        ), $attr);
        $output = '';
        $version = "";
        if ($default['version']) {
            $version = "?v=" . Hash::arrayUnset($default, 'version');
        }

        foreach ($src as $tag) {
            $attr = Hash::merge($default, array(
                        'src' => $this->url->content($tag) . $version
            ));
            $output .= $this->tag('script', null, $attr);
        }

        if ($inline) {
            return $output;
        } else {
            $this->scriptsForLayout .= $output;
        }
    }

    public function nestedList($list, $attr = array(), $type = 'ul')
    {
        $content = '';
        foreach ($list as $k => $li) {
            if (is_array($li)) {
                $li = $this->nestedList($li, array(), $type);
                if (!is_numeric($k)) {
                    $li = $k . $li;
                }
            }
            $content .= $this->tag('li', $li) . PHP_EOL;
        }

        return $this->tag($type, $content, $attr);
    }

    public function charset($charset = null)
    {
        if (is_null($charset)) {
            $charset = Config::read('App.encoding');
        }

        $attr = array(
            'charset' => $charset
        );

        return $this->tag('meta', null, $attr, TagRenderMode::SELF_CLOSING);
    }

    public function button($text, $type = HtmlButtonType::SUBMIT, $onclick = null, $attributes = array())
    {
        return ButtonBuilder::button(null, $text, $type, $onclick, $attributes);
    }

}
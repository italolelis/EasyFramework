<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Helper;

use Easy\Mvc\View\Builders\ButtonBuilder;
use Easy\Mvc\View\Builders\HtmlButtonType;
use Easy\Mvc\View\Builders\TagBuilder;
use Easy\Mvc\View\Builders\TagRenderMode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HtmlHelper
{

    /**
     * The Url Helper Object
     * @var UrlHelper 
     */
    public $url;

    public function __construct(UrlGeneratorInterface $url)
    {
        $this->url = $url;
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

    public function stylesheet($href, $attr = array())
    {
        if (!is_array($href)) {
            $href = array($href);
        }
        $default = array_merge(array(
            'href' => '',
            'rel' => 'stylesheet',
            'type' => 'text/css'
                ), $attr);

        $output = '';
        foreach ($href as $tag) {
            $attr = array_merge($default, array(
                'href' => $this->filterUrl($tag),
            ));
            $output .= $this->tag('link', null, $attr, TagRenderMode::SELF_CLOSING);
        }

        return $output;
    }

    public function script($src, $attr = array())
    {
        if (!is_array($src)) {
            $src = array($src);
        }

        $output = '';
        foreach ($src as $tag) {
            $attr = array_merge($attr, array(
                'src' => $this->filterUrl($tag)
            ));
            $output .= $this->tag('script', null, $attr);
        }

        return $output;
    }

    public function filterUrl($url)
    {
        if (strstr($url, "http://")) {
            return $url;
        } else {
            return $this->url->content($url);
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
            $charset = 'utf-8';
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
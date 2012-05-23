<?php

App::uses('TagBuilder', 'View/Builders');
App::uses('TagRenderMode', 'View/Builders');
App::uses('ButtonBuilder', 'View/Builders');
App::uses('HtmlButtonType', 'View/Builders');

class HtmlHelper extends AppHelper {

    public $scriptsForLayout = '';
    public $stylesForLayout = '';

    /**
     * The Url Helper Object
     * @var UrlHelper 
     */
    public $Url;

    public function __construct(HelperCollection $helpers) {
        parent::__construct($helpers);
        $this->Url = $this->Helpers->load('Url');
    }

    public function tag($tag, $content = '', $attr = null, $mode = TagRenderMode::NORMAL) {
        $tag = new TagBuilder($tag);
        $tag->mergeAttributes($attr);
        $tag->setInnerHtml($content);
        return $tag->toString($mode);
    }

    public function div($class, $content, $type) {
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

    public function span($message, array $attributes = array()) {
        if (!empty($message)) {
            $message = "<br/>" . $this->tag('span', $message, $attributes);
        } else {
            $message = "";
        }
        return $message;
    }

    public function actionLink($text, $action, $controller = null, $params = array(), $attr = array()) {
        $attr['href'] = $this->Url->action($action, $controller, $params);
        return $this->tag('a', $text, $attr);
    }

    public function link($text, $url = null, $attr = array(), $full = true) {
        if (is_null($url)) {
            $url = $text;
        }
        if (!isset($attr['href'])) {
            $attr['href'] = $this->Url->content($url, $full);
        }

        return $this->tag('a', $text, $attr);
    }

    public function image($src, $attr = array()) {
        $attr += array(
            'alt' => '',
            'title' => array_key_exists('alt', $attr) ? $attr['alt'] : ''
        );

        $attr['src'] = $this->assets->image($src);

        return $this->tag('img', null, $attr, true);
    }

    public function imagelink($src, $url, $img_attr = array(), $attr = array(), $full = false) {
        $image = $this->image($src, $img_attr);
        return $this->link($image, $url, $attr, $full);
    }

    public function stylesheet($href, $inline = true) {
        if (!is_array($href)) {
            $href = array($href);
        }
        $output = '';
        foreach ($href as $tag) {
            $attr = array(
                'href' => $this->Url->content($tag),
                'rel' => 'stylesheet',
                'type' => 'text/css'
            );
            $output .= $this->tag('link', null, $attr, TagRenderMode::SELF_CLOSING);
        }

        if ($inline) {
            return $output;
        } else {
            $this->stylesForLayout .= $output;
        }
    }

    public function script($src, $inline = true) {
        if (!is_array($src)) {
            $src = array($src);
        }
        $output = '';
        foreach ($src as $tag) {
            $attr = array(
                'src' => $this->Url->content($tag)
            );
            $output .= $this->tag('script', null, $attr);
        }

        if ($inline) {
            return $output;
        } else {
            $this->scriptsForLayout .= $output;
        }
    }

    public function nestedList($list, $attr = array(), $type = 'ul') {
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

    public function charset($charset = null) {
        if (is_null($charset)) {
            $charset = Config::read('App.encoding');
        }

        $attr = array(
            'charset' => $charset
        );

        return $this->tag('meta', null, $attr, TagRenderMode::SELF_CLOSING);
    }

    public function button($text, $type = HtmlButtonType::SUBMIT, $onclick = null ,array $attributes = array()) {
        return ButtonBuilder::button($text, $text, $type, $onclick, $attributes);
    }

}
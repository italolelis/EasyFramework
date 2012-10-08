<?php

namespace Easy\View\Helper;

use Easy\Security\Sanitize;
use Easy\Utility\Hash;
use Easy\Utility\Inflector;
use Easy\View\Builders\ButtonBuilder;
use Easy\View\Builders\HtmlButtonType;
use Easy\View\Builders\TagRenderMode;
use Easy\View\Controls\SelectList;
use Easy\View\Controls\SelectListItemRender;
use Easy\View\HelperCollection;

if (function_exists('lcfirst') === false) {

    function lcfirst($str)
    {
        return (string) (strtolower(substr($str, 0, 1)) . substr($str, 1));
    }

}

class FormHelper extends AppHelper
{

    protected $session;

    /**
     * The HTML Helper Object
     * @var HtmlHelper 
     */
    protected $Html;

    public function __construct(HelperCollection $helpers)
    {
        parent::__construct($helpers);
        $this->session = $this->Helpers->load('Session');
        $this->Html = $this->Helpers->load('Html');
    }

    public function create($action, $controller, $params = null, $htmlOptions = array())
    {
        if (!empty($params)) {
            $params = (Array) $params;
            $params = implode('/', $params);
        }

        $htmlOptions += array(
            'method' => 'post',
            'action' => $this->Html->Url->action($action, $controller, $params)
        );

        if ($htmlOptions['method'] == 'file') {
            $htmlOptions['method'] = 'post';
            $htmlOptions['enctype'] = 'multipart/form-data';
        }

        return $this->Html->tag('form', null, $htmlOptions, TagRenderMode::START_TAG);
    }

    public function close($submit = null, $attributes = array())
    {
        $form = $this->Html->tag('form', null, null, TagRenderMode::END_TAG);

        if (!is_null($submit)) {
            $form = $this->submit($submit, $attributes) . $form;
        }

        return $form;
    }

    public function submit($text, $attributes = array())
    {
        $attributes = Hash::merge(array(
                    'type' => 'submit',
                    'tag' => 'button'
                        ), $attributes);
        
        $attr = Hash::arrayUnset($attributes, 'tag');
        switch ($attr) {
            case 'image':
                $attributes['alt'] = $text;
                $attributes['type'] = 'image';
                $attributes['src'] = $this->assets->image($attributes['src']);
            case 'input':
                $attributes['value'] = $text;
                return ButtonBuilder::submitButton($text, $text, $attributes);
            default:
                return $this->Html->button($text, HtmlButtonType::SUBMIT, null, $attributes);
        }
    }

    public function reset($text, $attributes = array())
    {
        $attributes += array(
            'type' => 'reset',
            'tag' => 'button'
        );

        switch (Hash::arrayUnset($attributes, 'tag')) {
            case 'input':
                $attributes['value'] = $text;
                return $this->html->tag('input', '', $attributes, true);
            default:
                return $this->Html->button($text, $attributes);
        }
    }

    public function dropDownList(SelectList $object, $name = null, $htmlOptions = array())
    {
        $default = array(
            'id' => $name,
            'name' => $name,
            'selected' => null,
            'div' => false,
            'defaultText' => null
        );

        $htmlOptions = Hash::merge($default, $htmlOptions);
        $selected = Hash::arrayUnset($htmlOptions, 'selected');
        $div = Hash::arrayUnset($htmlOptions, 'div');
        $defaultText = Hash::arrayUnset($htmlOptions, 'defaultText');

        $list = new SelectListItemRender($object);
        $content = $list->render($selected, $defaultText);

        $input = $this->Html->tag('select', $content, $htmlOptions);

        if ($div) {
            $input = $this->Html->div($div, $input, 'select');
        }

        return $input;
    }

    public function dropDownListLabel(SelectList $object, $name = '', $inputOpt = array(), $labelOpt = array())
    {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->dropDownList($object, $name, $inputOpt);

        return $input;
    }

    public function dropDownListFor(SelectList $object, $selected = null, $name = '', $htmlOptions = array())
    {
        $htmlOptions = Hash::merge(array('selected' => $selected), $htmlOptions);
        return $this->dropDownList($object, $name, $htmlOptions);
    }

    public function dropDownListLabelFor($object, $selected = null, $name = '', $inputOpt = array(), $labelOpt = array())
    {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->dropDownListFor($object, $selected, $name, $inputOpt);

        return $input;
    }

    public function label($text, $for = null, array $options = array())
    {
        $default = array(
            'for' => $for === null ? lcfirst(Inflector::camelize($text)) : $for,
            'text' => Inflector::humanize($text)
        );
        $options = Hash::merge($default, $options);
        $text = Hash::arrayUnset($options, 'text');

        return $this->Html->tag('label', $text, $options);
    }

    public function labelFor($model, $text, $for = null, array $options = array())
    {
        $label = $this->label($text, $for, $options);
        $label .= $this->label($model);
        return $label;
    }

    public function inputText($name, $options = array())
    {
        $default = array(
            'type' => 'text',
            'id' => $name,
            'name' => $name,
            'div' => false,
            'message' => ""
        );

        $options = Hash::merge($default, $options);
        $message = Hash::arrayUnset($options, 'message');
        $div = Hash::arrayUnset($options, 'div');
        $type = $options['type'];

        $input = $this->Html->tag('input', null, $options, TagRenderMode::SELF_CLOSING) . $this->Html->span($message);

        if ($div) {
            $input = $this->Html->div($div, $input, $type);
        }

        return $input;
    }

    public function inputTextLabel($name, $inputOpt = array(), $labelOpt = array())
    {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->inputText($name, $inputOpt);

        return $input;
    }

    public function inputTextFor($model, $name, $options = array())
    {
        $default = array(
            'value' => Sanitize::html($model)
        );
        $options = Hash::merge($default, $options);
        return $this->inputText($name, $options);
    }

    public function inputTextLabelFor($model, $name, $inputOpt = array(), $labelOpt = array())
    {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->inputTextFor($model, $name, $inputOpt);

        return $input;
    }

    public function inputPassword($name, $htmlOptions = null)
    {
        $default = array(
            'type' => 'password'
        );
        $htmlOptions = Hash::merge($default, $htmlOptions);
        return $this->inputText($name, $htmlOptions);
    }

    public function inputPasswordlabel($name, $inputOpt = null, $labelOpt = null)
    {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->inputPassword($name, $inputOpt);

        return $input;
    }

    public function textArea($name, $htmlOptions = array())
    {
        $default = array(
            'id' => $name,
            'name' => $name,
            'div' => false,
            'message' => ""
        );
        $htmlOptions = Hash::merge($default, $htmlOptions);

        $div = Hash::arrayUnset($htmlOptions, 'div');
        $message = Hash::arrayUnset($htmlOptions, 'message');
        $value = Hash::arrayUnset($htmlOptions, 'value');

        $input = $this->Html->tag('textarea', $value, $htmlOptions) . $this->Html->span($message);

        if ($div) {
            $input = $this->Html->div($div, $input, 'textarea');
        }

        return $input;
    }

    public function textAreaLabel($name, $inputOpt = array(), $labelOpt = array())
    {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->textArea($name, $inputOpt);

        return $return;
    }

    public function textAreaFor($model, $name, $options = array())
    {
        $default = array(
            'value' => Sanitize::html($model)
        );
        $options = Hash::merge($default, $options);
        return $this->textArea($name, $options);
    }

    public function textAreaLabelFor($model, $name, $inputOpt = array(), $labelOpt = array())
    {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->textAreaFor($model, $name, $inputOpt);

        return $return;
    }

    public function checkbox($name, $options = array())
    {
        $default = array(
            'id' => $name,
            'name' => $name,
            'type' => 'checkbox'
        );

        $options = Hash::merge($default, $options);

        $value = Hash::arrayUnset($options, 'value');
        return $this->Html->tag('input', $value, $options, TagRenderMode::SELF_CLOSING);
    }

    public function checkboxLabel($name, $inputOpt = array(), $labelOpt = array())
    {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->checkbox($name, $inputOpt);
        return $return;
    }

    public function checkboxFor($model, $name, $options = array())
    {
        $default = array();
        if ($model == true) {
            $default = array(
                'checked' => $model
            );
        }
        $options = Hash::merge($default, $options);
        return $this->checkbox($name, $options);
    }

    public function checkboxLabelFor($model, $name, $inputOpt = array(), $labelOpt = array())
    {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->checkboxFor($model, $name, $inputOpt);
        return $return;
    }

    public function setErrors($errors, $key = 'form')
    {
        return $this->view->getController()->Session->setFlash($errors, $key);
    }

    public function getErrors($key = 'flash', array $attrs = array())
    {
        return $this->session->flash($key, $attrs);
    }

}
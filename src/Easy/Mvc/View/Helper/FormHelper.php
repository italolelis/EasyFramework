<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Helper;

use Easy\Mvc\View\Builders\ButtonBuilder;
use Easy\Mvc\View\Builders\HtmlButtonType;
use Easy\Mvc\View\Builders\TagRenderMode;
use Easy\Mvc\View\Controls\SelectList;
use Easy\Mvc\View\Controls\SelectListItemRender;
use Easy\Security\Sanitize;
use Easy\Storage\Session\SessionInterface;
use Easy\Utility\Hash;
use Easy\Utility\Inflector;

if (function_exists('lcfirst') === false) {

    function lcfirst($str)
    {
        return (string) (strtolower(substr($str, 0, 1)) . substr($str, 1));
    }

}

/**
 * The Form Helper is used to build form and form elements in the view
 */
class FormHelper
{

    /**
     * @var SessionInterface The Session Helper Object 
     */
    protected $session;

    /**
     * @var HtmlHelper The HTML Helper Object
     */
    protected $html;

    public function __construct(SessionInterface $session, HtmlHelper $html)
    {
        $this->session = $session;
        $this->html = $html;
    }

    /**
     * Creates a form tag
     * @param string $action The action to create the URL
     * @param string $controller The controller to create the URL
     * @param mixed $params The parameters to create the URL
     * @param array $htmlAttributes Any html attributes
     * @return string The form open tag
     */
    public function create($route_name, $parameters = array(), array $htmlAttributes = array())
    {
        $htmlAttributes += array(
            'method' => 'post',
            'action' => $this->html->url->generate($route_name, $parameters)
        );

        if ($htmlAttributes['method'] == 'file') {
            $htmlAttributes['method'] = 'post';
            $htmlAttributes['enctype'] = 'multipart/form-data';
        }

        return $this->html->tag('form', null, $htmlAttributes, TagRenderMode::START_TAG);
    }

    /**
     * Closes a form tag
     * @param bool $submit Whether the submit button is generated or not
     * @param array $attributes Any html attributes
     * @return string The form close tag
     */
    public function close($submit = null, $attributes = array())
    {
        $form = $this->html->tag('form', null, null, TagRenderMode::END_TAG);

        if (!is_null($submit)) {
            $form = $this->submit($submit, $attributes) . $form;
        }

        return $form;
    }

    /**
     * Generates a submit button
     * @param string $text The submit button text
     * @param array $attributes Any html attributes
     * @return string The submit button tag
     */
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
                return $this->html->button($text, HtmlButtonType::SUBMIT, null, $attributes);
        }
    }

    /**
     * Generates a reset button
     * @param string $text The submit button text
     * @param array $attributes Any html attributes
     * @return string The reset button tag
     */
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
                return $this->html->button($text, $attributes);
        }
    }

    /**
     * Generates a select input
     * @param SelectList $object The SelectList object
     * @param string $name The name of the input
     * @param array $attributes Any input attributes
     * @return string The select input tag
     */
    public function dropDownList(SelectList $object, $name = null, $attributes = array())
    {
        $default = array(
            'id' => $name,
            'name' => $name,
            'selected' => null,
            'div' => false,
            'defaultText' => null
        );

        $attributes = Hash::merge($default, $attributes);
        $selected = Hash::arrayUnset($attributes, 'selected');
        $div = Hash::arrayUnset($attributes, 'div');
        $defaultText = Hash::arrayUnset($attributes, 'defaultText');

        $list = new SelectListItemRender($object);
        $content = $list->render($selected, $defaultText);

        $input = $this->html->tag('select', $content, $attributes);

        if ($div) {
            $input = $this->html->div($div, $input, 'select');
        }

        return $input;
    }

    /**
     * Generates a select input with a label
     * @param SelectList $object The SelectList object
     * @param string $name The name of the input
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The select input tag
     */
    public function dropDownListLabel(SelectList $object, $name = '', array $inputAttributes = array(), array $labelAttributes = array())
    {
        $input = $this->label($name, $name, $labelAttributes);
        $input .= $this->dropDownList($object, $name, $inputAttributes);
        return $input;
    }

    /**
     * Generates a select input for a value
     * @param SelectList $object The SelectList object
     * @param mixed $selected The value to be selected on the list
     * @param string $name The name of the input
     * @param array $htmlAttributes Any input attributes
     * @return string The select input tag
     */
    public function dropDownListFor(SelectList $object, $selected = null, $name = '', array $htmlAttributes = array())
    {
        $htmlAttributes = Hash::merge(array(
                    'selected' => $selected
                        ), $htmlAttributes);
        return $this->dropDownList($object, $name, $htmlAttributes);
    }

    /**
     * Generates a select input with a label
     * @param SelectList $object
     * @param mixed $selected The value to be selected on the list
     * @param string $name The name of the input
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The select input tag
     */
    public function dropDownListLabelFor(SelectList $object, $selected = null, $name = '', $inputAttributes = array(), $labelAttributes = array())
    {
        $input = $this->label($name, $name, $labelAttributes);
        $input .= $this->dropDownListFor($object, $selected, $name, $inputAttributes);

        return $input;
    }

    /**
     * Generates a label tag
     * @param string $text The label's name
     * @param string $for The for attribute
     * @param array $options Any input attributes
     * @return string The label input tag
     */
    public function label($text, $for = null, array $options = array())
    {
        $default = array(
            'for' => $for === null ? lcfirst(Inflector::camelize($text)) : $for, 'text' => Inflector::humanize($text)
        );
        $options = Hash

                ::merge($default, $options);
        $text = Hash:: arrayUnset($options, 'text');

        return $this->html->tag('label', $text, $options);
    }

    /**
     * Generates a label tag for a value
     * @param mixed $model
     * @param string $text The label's name
     * @param string $for The for attribute
     * @param array $options Any input attributes
     * @return string The label input tag
     */
    public function labelFor($model, $text, $for = null, array $options =
    array())
    {
        $label = $this->label($text, $for, $options);
        $label .= $this->label($model);
        return $label;
    }

    /**
     * Generates an input tag
     * @param string $name The input's name
     * @param array $options Any input attributes
     * @return string The input tag 
     */
    public function inputText($name, array $options = array())
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

        $input = $this->html->tag('input', null, $options, TagRenderMode::SELF_CLOSING) .
                $this->html->span($message);

        if ($div) {
            $input = $this->html->div($div, $input, $type);
        }

        return $input;
    }

    /**
     * Generates an input tag with label
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function inputTextLabel($name, array $inputAttributes = array(), array $labelAttributes = array())
    {
        $input = $this->label($name, $name, $labelAttributes);
        $input .= $this->inputText($name, $inputAttributes);

        return $input;
    }

    /**
     * Generates an input tag for a value
     * @param mixed $model The value to be used in the input
     * @param string $name The input's name
     * @param array $options Any input attributes
     * @return string The input tag
     */
    public function inputTextFor($model, $name, array $options = array())
    {
        $default = array('value'
            => Sanitize::html($model)
        );
        $options = Hash::merge($default, $options);
        return $this->inputText($name, $options);
    }

    /**
     * Generates an input tag with label and a value
     * @param mixed $model he value to be used in the input
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function inputTextLabelFor($model, $name, array $inputAttributes = array(), array $labelAttributes = array())
    {

        $input = $this->label($name, $name, $labelAttributes);
        $input .= $this->inputTextFor($model, $name, $inputAttributes);

        return $input;
    }

    /**
     * Generates a password input
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @return string The input tag
     */
    public function inputPassword($name, array $inputAttributes = null)
    {
        $default = array('type'
            => 'password'
        );
        $inputAttributes = Hash:: merge($default, $inputAttributes);
        return $this->inputText($name, $inputAttributes);
    }

    /**
     * Generates a password input with label
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function inputPasswordlabel($name, array $inputAttributes = null, array $labelAttributes = null)
    {
        $input = $this->label($name, $name, $labelAttributes);
        $input .= $this->inputPassword($name, $inputAttributes);

        return $input;
    }

    /**
     * Generates a text area input
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @return string The input tag
     */
    public function textArea($name, array $inputAttributes = array())
    {
        $default = array(
            'id' => $name,
            'name' => $name,
            'div' => false,
            'message' => ""
        );
        $inputAttributes = Hash::merge($default, $inputAttributes);

        $div = Hash::arrayUnset($inputAttributes, 'div');
        $message = Hash::arrayUnset($inputAttributes, 'message');
        $value = Hash::arrayUnset($inputAttributes, 'value');

        $input = $this->html->tag('textarea', $value, $inputAttributes) . $this->
                html->span($message);

        if ($div) {
            $input = $this->html->div($div, $input, 'textarea');
        }

        return $input;
    }

    /**
     * Generates a text area input with label
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function textAreaLabel($name, array $inputAttributes = array(), array $labelAttributes = array())
    {
        $return = $this->label($name, $name, $labelAttributes);
        $return .= $this->textArea($name, $inputAttributes);

        return $return;
    }

    /**
     * Generates a text area input with a value
     * @param mixed $model The value to be used with the input
     * @param string $name The input's name
     * @param array $options Any input attributes
     * @return string The input tag
     */
    public function textAreaFor($model, $name, array $options = array())
    {
        $default = array('value'
            => Sanitize::html($model)
        );
        $options = Hash::merge($default, $options);
        return $this->textArea($name, $options);
    }

    /**
     * Generates a text area input with label and value
     * @param type $model
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function textAreaLabelFor($model, $name, array $inputAttributes = array(), array $labelAttributes = array())
    {

        $return = $this->label($name, $name, $labelAttributes);
        $return .= $this->textAreaFor($model, $name, $inputAttributes);

        return $return;
    }

    /**
     * Generates a checkbox input
     * @param string $name The input's name
     * @param array $options Any input attributes
     * @return string The input tag
     */
    public function checkbox($name, array $options = array())
    {
        $default = array('id' => $name,
            'name' => $name,
            'type' => 'checkbox'
        );

        $options = Hash::merge($default, $options);

        //$value = Hash::arrayUnset($options, 'value');
        return $this->html->tag('input', null, $options, TagRenderMode::SELF_CLOSING);
    }

    /**
     * Generates a checkbox input with label
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function checkboxLabel($name, array $inputAttributes = array(), array $labelAttributes = array())
    {
        $return = $this->checkbox($name, $inputAttributes);
        $return .= $this->label($name, $name, $labelAttributes);
        return $return;
    }

    /**
     * Generates a checkbox input weith value
     * @param mixed $model The value to be checked
     * @param string $name The input's name
     * @param array $options Any input attributes
     * @return string The input tag
     */
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

    /**
     * Generates a checkbox input with label and value
     * @param mixed $model The value to be checked
     * @param string $name The input's name
     * @param array $inputAttributes Any input attributes
     * @param array $labelAttributes Any label attributes
     * @return string The input tag
     */
    public function checkboxLabelFor($model, $name, array $inputAttributes = array(), array $labelAttributes = array())
    {
        $return = $this->checkboxFor($model, $name, $inputAttributes);
        $return .= $this->label($name, $name, $labelAttributes);
        return $return;
    }

    /**
     * Creates a wrapper with a tag
     * @param string $tag The tag to be created
     * @param mixed $content The content to be in the tag
     * @param array $options The 
     * @return string The input tag
     */
    public function createWrapper($tag, $content, array $options = null)
    {
        return $this->html->tag($tag, $content, $options);
    }

}
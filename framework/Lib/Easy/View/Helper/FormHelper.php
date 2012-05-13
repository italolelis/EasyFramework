<?php

App::uses('Sanitize', "Security");
App::uses('Inflector', "Commom");
App::uses('Hash', "Utility");

class FormHelper extends AppHelper {

    protected $html;
    protected $session;

    public function __construct(HelperCollection $helpers) {
        parent::__construct($helpers);
        $this->html = $this->Helpers->load('Html');
        $this->session = $this->Helpers->load('Session');
    }

    public function create($action, $controller, $params = null, $options = array()) {
        if (!empty($params)) {
            $params = (Array) $params;
            $params = "/" . implode('/', $params);
        }

        $options += array(
            'method' => 'post',
            'action' => Mapper::url("/" . $controller . "/" . $action . $params)
        );

        if ($options['method'] == 'file') {
            $options['method'] = 'post';
            $options['enctype'] = 'multipart/form-data';
        }

        return $this->html->openTag('form', $options);
    }

    public function close($submit = null, $attributes = array()) {
        $form = $this->html->closeTag('form');

        if (!is_null($submit)) {
            $form = $this->submit($submit, $attributes) . $form;
        }

        return $form;
    }

    public function submit($text, $attributes = array()) {
        $attributes += array(
            'type' => 'submit',
            'tag' => 'button'
        );

        switch (Hash::arrayUnset($attributes, 'tag')) {
            case 'image':
                $attributes['alt'] = $text;
                $attributes['type'] = 'image';
                $attributes['src'] = $this->assets->image($attributes['src']);
            case 'input':
                $attributes['value'] = $text;
                return $this->html->tag('input', '', $attributes, true);
            default:
                return $this->html->tag('button', $text, $attributes);
        }
    }

    public function reset($text, $attributes = array()) {
        $attributes += array(
            'type' => 'reset',
            'tag' => 'button'
        );

        switch (Hash::arrayUnset($attributes, 'tag')) {
            case 'input':
                $attributes['value'] = $text;
                return $this->html->tag('input', '', $attributes, true);
            default:
                return $this->html->tag('button', $text, $attributes);
        }
    }

    public function dropDownList($object, $name = '', array $options = array()) {
        $default = array(
            'id' => $name,
            'name' => $name,
            'selected' => null,
            'div' => true,
            'defaultText' => null
        );

        $options = Hash::merge($default, $options);
        $selected = Hash::arrayUnset($options, 'selected');
        $div = Hash::arrayUnset($options, 'div');
        $defaultText = Hash::arrayUnset($options, 'defaultText');

        $content = '';
        if (!empty($selected)) {
            foreach ($object as $key => $value) {
                $option = array('value' => $key);
                if ((string) $key === (string) $selected) {
                    $option['selected'] = true;
                }
                $content .= $this->html->tag('option', $value, $option);
            }
        } else {
            if (!empty($defaultText)) {
                $content .= $this->html->tag('option', $defaultText);
            }
            foreach ($object as $key => $value) {
                $option = array('value' => $key);
                $content .= $this->html->tag('option', $value, $option);
            }
        }

        $input = $this->html->tag('select', $content, $options);
        if ($div) {
            $input = $this->div($div, $input, 'select');
        }

        return $input;
    }

    public function dropDownListLabel($object, $name = '', array $inputOpt = array(), array $labelOpt = array()) {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->dropDownList($object, $name, $inputOpt);

        return $input;
    }

    public function dropDownListFor($object, $selected = null, $name = '', array $options = array()) {
        $options = Hash::merge(array('selected' => $selected), $options);
        return $this->dropDownList($object, $name, $options);
    }

    public function dropDownListLabelFor($object, $selected = null, $name = '', array $inputOpt = array(), array $labelOpt = array()) {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->dropDownListFor($object, $selected, $name, $inputOpt);

        return $input;
    }

    public function label($text, $for = null, array $options = array()) {
        $default = array(
            'for' => $for === null ? lcfirst(Inflector::camelize($text)) : $for,
            'text' => Inflector::humanize($text)
        );
        $options = Hash::merge($default, $options);
        $text = Hash::arrayUnset($options, 'text');

        $label = $this->html->tag('label', $text, $options);

        return $label;
    }

    public function labelFor($model, $text, $for = null, array $options = array()) {
        $label = $this->label($text, $for, $options);
        $label .= $this->label($model);
        return $label;
    }

    public function inputText($name, $options = array()) {
        $default = array(
            'type' => 'text',
            'id' => $name,
            'name' => $name,
            'div' => true,
            'message' => ""
        );

        $options = Hash::merge($default, $options);

        $message = Hash::arrayUnset($options, 'message');
        $div = Hash::arrayUnset($options, 'div');
        $type = $options['type'];

        if (!empty($message)) {
            $message = "<br/><span>" . $message . "</span>";
        } else {
            $message = "";
        }

        $input = $this->html->tag('input', '', $options, true) . $message;
        if ($div) {
            $input = $this->div($div, $input, $type);
        }

        return $input;
    }

    public function inputTextLabel($name, $inputOpt = array(), $labelOpt = array()) {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->inputText($name, $inputOpt);

        return $input;
    }

    public function inputTextFor($model, $name, $options = array()) {
        $default = array(
            'value' => Sanitize::html($model)
        );
        $options = Hash::merge($default, $options);
        return $this->inputText($name, $options);
    }

    public function inputTextLabelFor($model, $name, $inputOpt = array(), $labelOpt = array()) {
        $input = $this->label($name, $name, $labelOpt);
        $input .= $this->inputTextFor($model, $name, $inputOpt);

        return $input;
    }

    public function textArea($name, $options = array()) {
        $default = array(
            'id' => $name,
            'name' => $name,
            'div' => true,
            'message' => ""
        );
        $options = Hash::merge($default, $options);

        $div = Hash::arrayUnset($options, 'div');
        $message = Hash::arrayUnset($options, 'message');
        $value = Hash::arrayUnset($options, 'value');

        if (!empty($message)) {
            $message = "<br/><span>" . $message . "</span>";
        } else {
            $message = "";
        }

        $input = $this->html->tag('textarea', $value, $options) . $message;

        if ($div) {
            $input = $this->div($div, $input, 'textarea');
        }

        return $input;
    }

    public function textAreaLabel($name, $inputOpt = array(), $labelOpt = array()) {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->textArea($name, $inputOpt);

        return $return;
    }

    public function textAreaFor($model, $name, $options = array()) {
        $default = array(
            'value' => Sanitize::html($model)
        );
        $options = Hash::merge($default, $options);
        return $this->textArea($name, $options);
    }

    public function textAreaLabelFor($model, $name, $inputOpt = array(), $labelOpt = array()) {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->textAreaFor($model, $name, $inputOpt);

        return $return;
    }

    protected function div($class, $content, $type) {
        $attr = array(
            'class' => 'input ' . $type
        );

        if (is_array($class)) {
            $attr = $class + $attr;
        } elseif (is_string($class)) {
            $attr['class'] .= ' ' . $class;
        }

        return $this->html->tag('div', $content, $attr);
    }

    public function setErrors($errors, $key = 'form') {
        return $this->view->getController()->Session->setFlash($errors, $key);
    }

    public function getErrors($key = 'flash', array $attrs = array()) {
        return $this->session->flash($key, $attrs);
    }

}
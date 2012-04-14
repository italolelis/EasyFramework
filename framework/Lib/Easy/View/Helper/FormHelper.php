<?php

App::uses('Sanitize', "Security");
App::uses('Inflector', "Commom");
App::uses('HtmlHelper', "Helper");

class FormHelper extends AppHelper {

    protected $html;

    public function __construct($view) {
        parent::__construct($view);
        $this->html = new HtmlHelper($view);
    }

    public function create($action, $controller, $params = null, $options = array()) {
        if (!empty($params)) {
            $params = (Array) $params;
            $params = "/" . implode('/', $params);
        }

        $options += array(
            'method' => 'post',
            'action' => Mapper::url("/" . $action . "/" . $controller . $params)
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

        switch (array_unset($attributes, 'tag')) {
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

    public function select($name, $object, $selected = null, array $options = array()) {
        $default = array(
            'id' => $name,
            'name' => $name,
        );
        $options = Set::merge($default, $options);

        $tag = "<select ";
        foreach ($options as $key => $value) {
            $tag .= "{$key}='{$value}'";
        }
        $tag .= ">";

        foreach ($object as $key => $value) {
            if ($selected === $value) {
                $tag .= "<option value='{$key}' selected='selected'>{$value}</option>";
            } else {
                $tag .= "<option value='{$key}'>{$value}</option>";
            }
        }

        $tag .= "</select>";

        return $tag;
    }

    public function label($text, $for = null, array $options = array()) {
        $default = array(
            'for' => $for === null ? lcfirst(Inflector::camelize($text)) : $for,
            'text' => Inflector::humanize($text)
        );
        $options = Set::merge($default, $options);

        $tag = "<label ";
        foreach ($options as $key => $value) {
            if ($key != 'text') {
                $tag .= "{$key}='{$value}'";
            }
        }
        $tag .= ">{$options['text']}</label>";

        return $tag;
    }

    public function input($name, $options = array()) {
        $default = array(
            'type' => 'text',
            'id' => $name,
            'name' => $name,
            'div' => true,
            'message' => ""
        );
        $options = Set::merge($default, $options);

        if ($options['div']) {
            $beginDiv = "<div>";
            $endDiv = "</div>";
        } else {
            $beginDiv = "";
            $endDiv = "";
        }
        if (!empty($options['message'])) {
            $message = "<br/><span>" . $options['message'] . "</span>";
        } else {
            $message = "";
        }

        $tag = $beginDiv . "<input ";
        foreach ($options as $key => $value) {
            if ($key !== 'div' && $key !== 'message') {
                $tag .= "{$key}='{$value}'";
            }
        }
        $tag .= ">" . $message . $endDiv;

        return $tag;
    }

    public function inputFor($model, $name, $options = array()) {
        $default = array(
            'value' => $model
        );
        $options = Set::merge($default, $options);
        return $this->input($name, $options);
    }

    public function inputLabelFor($model, $name, $inputOpt = array(), $labelOpt = array()) {
        $return = $this->label($name, $name, $labelOpt);
        $return .= $this->inputFor($model, $name, $inputOpt);

        return $return;
    }

    public function textArea($name, $options = array()) {
        $default = array(
            'id' => $name,
            'name' => $name,
            'div' => true,
            'message' => ""
        );
        $options = Set::merge($default, $options);

        if ($options['div']) {
            $beginDiv = "<div>";
            $endDiv = "</div>";
        } else {
            $beginDiv = "";
            $endDiv = "";
        }
        if (!empty($options['message'])) {
            $message = "<br/><span>" . $options['message'] . "</span>";
        } else {
            $message = "";
        }

        $tag = $beginDiv . "<textarea ";
        foreach ($options as $key => $value) {
            if ($key !== 'div' && $key !== 'message' && $key !== 'value') {
                $tag .= "{$key}='{$value}'";
            }
        }
        $tag .= ">{$options['value']}</textarea>" . $message . $endDiv;

        return $tag;
    }

    public function textAreaFor($model, $name, $options = array()) {
        $default = array(
            'value' => $model
        );
        $options = Set::merge($default, $options);
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

}
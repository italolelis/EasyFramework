<?php

App::uses('TagRenderMode', 'View/Builders');

class TagBuilder
{

    const _ATTRIBUTE_FORMAT = "%s = '%s'";
    const _ELEMENT_FORMAT_END_TAG = "</%s>";
    const _ELEMENT_FORMAT_NORMAL = "<%s %s>%s</%s>";
    //const _ELEMENT_FORMAT_SELF_CLOSING = "<%s %s />";
    const _ELEMENT_FORMAT_SELF_CLOSING = "<%s %s >";
    const _ELEMENT_FORMAT_START_TAG = "<%s %s>";

    protected $attributes;
    protected $innerHtml;
    protected $tagName;

    function __construct($tagName)
    {
        if (empty($tagName)) {
            throw new InvalidArgumentException(__("Invalid Argument passed"));
        }
        $this->tagName = $tagName;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getInnerHtml()
    {
        return $this->innerHtml;
    }

    public function setInnerHtml($innerHtml)
    {
        $this->innerHtml = $innerHtml;
    }

    public function getTagName()
    {
        return $this->tagName;
    }

    public function setTagName($tagName)
    {
        $this->tagName = $tagName;
    }

    public function addCssClass($value)
    {
        if (isset($this->attributes["class"])) {
            $_currentValue = $this->attributes["class"];
            $this->attributes["class"] = $value + " " + $_currentValue;
        } else {
            $this->attributes["class"] = $value;
        }
    }

    private function getAttributesString()
    {
        if (!empty($this->attributes)) {
            $attributes = array();
            foreach ($this->attributes as $key => $value) {
                if ($value === true) {
                    $value = $key;
                }
                $attributes[] = sprintf(self::_ATTRIBUTE_FORMAT, $key, $value);
            }
            return join(' ', $attributes);
        } else {
            return null;
        }
    }

    public function mergeAttribute($key, $value, $replaceExisting = true)
    {
        if ($replaceExisting || !isset($this->attributes[$key])) {
            $this->attributes[$key] = $value;
        }
    }

    public function mergeAttributes($attributes, $replaceExisting = true)
    {
        if ($attributes !== null) {
            foreach ($attributes as $key => $value) {
                $this->mergeAttribute($key, $value, $replaceExisting);
            }
        }
    }

    public function __toString()
    {
        $this->toString(TagRenderMode::NORMAL);
    }

    public function toString($renderMode)
    {
        switch ($renderMode) {
            case TagRenderMode::START_TAG:
                return sprintf(self::_ELEMENT_FORMAT_START_TAG, $this->tagName, $this->getAttributesString());
            case TagRenderMode::END_TAG:
                return sprintf(self::_ELEMENT_FORMAT_END_TAG, $this->tagName);
            case TagRenderMode::SELF_CLOSING:
                return sprintf(self::_ELEMENT_FORMAT_SELF_CLOSING, $this->tagName, $this->getAttributesString());
            default:
                return sprintf(self::_ELEMENT_FORMAT_NORMAL, $this->tagName, $this->getAttributesString(), $this->innerHtml, $this->tagName);
        }
    }

}
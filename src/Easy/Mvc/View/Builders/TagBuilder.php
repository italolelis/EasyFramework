<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Builders;

use Easy\Collections\Dictionary;
use InvalidArgumentException;

class TagBuilder
{

    const ATTRIBUTE_FORMAT = "%s = '%s'";
    const ELEMENT_FORMAT_END_TAG = "</%s>";
    const ELEMENT_FORMAT_NORMAL = "<%s %s>%s</%s>";
    const ELEMENT_FORMAT_SELF_CLOSING = "<%s %s>";
    const ELEMENT_FORMAT_START_TAG = "<%s %s>";

    protected $attributes;
    protected $innerHtml;
    protected $tagName;

    function __construct($tagName)
    {
        if (empty($tagName)) {
            throw new InvalidArgumentException(__("Invalid Argument passed"));
        }
        $this->tagName = $tagName;
        $this->attributes = new Dictionary();
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
        if ($this->attributes->contains('class')) {
            $_currentValue = $this->attributes->getItem('class');
            $this->attributes->set('class', $value + " " + $_currentValue);
        } else {
            $this->attributes->set('class', $value);
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
                $attributes[] = sprintf(self::ATTRIBUTE_FORMAT, $key, $value);
            }
            return join(' ', $attributes);
        } else {
            return null;
        }
    }

    public function mergeAttribute($key, $value, $replaceExisting = true)
    {
        if ($replaceExisting || !$this->attributes->contains($key)) {
            $this->attributes->set($key, $value);
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
                return sprintf(self::ELEMENT_FORMAT_START_TAG, $this->tagName, $this->getAttributesString());
            case TagRenderMode::END_TAG:
                return sprintf(self::ELEMENT_FORMAT_END_TAG, $this->tagName);
            case TagRenderMode::SELF_CLOSING:
                return sprintf(self::ELEMENT_FORMAT_SELF_CLOSING, $this->tagName, $this->getAttributesString());
            default:
                return sprintf(self::ELEMENT_FORMAT_NORMAL, $this->tagName, $this->getAttributesString(), $this->innerHtml, $this->tagName);
        }
    }

}
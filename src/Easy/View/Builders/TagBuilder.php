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

namespace Easy\View\Builders;

class TagBuilder
{

    const _ATTRIBUTE_FORMAT = "%s = '%s'";
    const _ELEMENT_FORMAT_END_TAG = "</%s>";
    const _ELEMENT_FORMAT_NORMAL = "<%s %s>%s</%s>";
    //const _ELEMENT_FORMAT_SELF_CLOSING = "<%s %s />";
    const _ELEMENT_FORMAT_SELF_CLOSING = "<%s %s>";
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
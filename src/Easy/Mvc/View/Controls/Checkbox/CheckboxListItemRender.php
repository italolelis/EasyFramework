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

namespace Easy\Mvc\View\Controls\Checkbox;

use Easy\Mvc\View\Builders\TagBuilder;
use Easy\Mvc\View\Builders\TagRenderMode;
use Easy\Mvc\View\Controls\IRender;
use Easy\Utility\Hash;

class CheckboxListItemRender implements IRender
{

    private $items;
    private $htmlOptions;

    function __construct(CheckboxList $items, $options)
    {
        $this->items = $items;
        $this->htmlOptions = $options;
    }

    public function render($selected, $defaultText = null)
    {
        if (!empty($selected)) {
            $input = $this->renderSelected($selected);
        } else {
            $input = $this->renderElement();
        }

        return $input;
    }

    public function renderSelected($selected)
    {
        return $this->renderElement(function($value, $option) use ($selected) {
                            if (in_array($value, $selected)) {
                                $option['checked'] = true;
                            }
                        });
    }

    public function renderElement($fn = null)
    {
        $count = 0;
        foreach ($this->items->getItems() as $item) {
            $option = Hash::merge(array(
                        "type" => "checkbox",
                        'value' => $item->getValue()
                            ), $this->htmlOptions);

            $option["id"] = $option["id"] . "-" . $count++;

            if ($fn) {
                $fn($item->getValue(), $option);
            }

            $tag = new TagBuilder('input');
            $tag->mergeAttributes($option);
            $tag->setInnerHtml($item->getDisplay());

            $labelTag = new TagBuilder('label');
            $labelTag->setInnerHtml($item->getDisplay());
            $labelTag->addCssClass("label");
            $labelTag->mergeAttribute("for", $option["id"]);
            $inputFields[] = $tag->toString(TagRenderMode::SELF_CLOSING) . $labelTag->toString(TagRenderMode::NORMAL);
        }
        return join(' ', $inputFields);
    }

}
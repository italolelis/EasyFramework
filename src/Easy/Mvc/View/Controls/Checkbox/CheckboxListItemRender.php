<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Controls\Checkbox;

use Easy\Mvc\View\Builders\TagBuilder;
use Easy\Mvc\View\Builders\TagRenderMode;
use Easy\Mvc\View\Controls\RenderInterface;
use Easy\Utility\Hash;

class CheckboxListItemRender implements RenderInterface
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
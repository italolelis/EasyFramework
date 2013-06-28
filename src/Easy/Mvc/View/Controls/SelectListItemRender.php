<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Controls;

use Easy\Mvc\View\Builders\TagBuilder;
use Easy\Mvc\View\Builders\TagRenderMode;

class SelectListItemRender
{

    private $items;

    function __construct(SelectList $items)
    {
        $this->items = $items;
    }

    public function render($selected, $defaultText = null)
    {
        if (!empty($selected)) {
            $input = $this->renderSelected($selected);
        } else {
            $input = '';
            if (!empty($defaultText)) {
                $tag = new TagBuilder('option');
                $tag->setInnerHtml($defaultText);
                $input .= $tag->toString(TagRenderMode::NORMAL);
            }
            $optionTags = array();
            foreach ($this->items->getItems() as $item) {
                $option = array(
                    'value' => $item->getValue()
                );
                $tag = new TagBuilder('option');
                $tag->mergeAttributes($option);
                $tag->setInnerHtml($item->getDisplay());
                $optionTags[] = $tag->toString(TagRenderMode::NORMAL);
            }
            $input .= join(' ', $optionTags);
        }

        return $input;
    }

    public function renderSelected($selected)
    {
        $optionTags = array();
        foreach ($this->items->getItems() as $item) {
            $option = array(
                'value' => $item->getValue()
            );
            if ((string) $item->getValue() === (string) $selected) {
                $option['selected'] = true;
            }
            $tag = new TagBuilder('option');
            $tag->mergeAttributes($option);
            $tag->setInnerHtml($item->getDisplay());
            $optionTags[] = $tag->toString(TagRenderMode::NORMAL);
        }
        return join(' ', $optionTags);
    }

}
<?php

class SelectListItem {

    private $items;

    function __construct($items) {
        $this->items = $items;
    }

    public function render($selected, $defaultText = null) {
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
            foreach ($this->items as $key => $value) {
                $option = array(
                    'value' => $key
                );
                $tag = new TagBuilder('option');
                $tag->mergeAttributes($option);
                $tag->setInnerHtml($value);
                $optionTags[] = $tag->toString(TagRenderMode::NORMAL);
            }
            $input .= join(' ', $optionTags);
        }

        return $input;
    }

    public function renderSelected($selected) {
        $optionTags = array();
        foreach ($this->items as $key => $value) {
            $option = array(
                'value' => $key
            );
            if ((string) $key === (string) $selected) {
                $option['selected'] = true;
            }
            $tag = new TagBuilder('option');
            $tag->mergeAttributes($option);
            $tag->setInnerHtml($value);
            $optionTags[] = $tag->toString(TagRenderMode::NORMAL);
        }
        return join(' ', $optionTags);
    }

}
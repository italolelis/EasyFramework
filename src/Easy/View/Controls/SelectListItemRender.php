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

namespace Easy\View\Controls;

use Easy\View\Builders\TagBuilder;
use Easy\View\Builders\TagRenderMode;

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
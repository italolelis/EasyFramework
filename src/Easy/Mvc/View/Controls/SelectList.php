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

namespace Easy\Mvc\View\Controls;

use Easy\Mvc\View\Controls\SelectItem;
use Easy\Collections\Dictionary;
use Easy\Collections\Collection;

/**
 * Represents a list that lets users select one item.
 */
class SelectList
{

    /**
     * The Collection of arrays or objects to handle
     * @var Dictionary <Object>
     */
    private $list;

    /**
     * @var Collection <SelectItem> The collection of SelectItem
     */
    private $items = array();

    /**
     * The field that's gonna be the value on the SelectItem
     * @var string 
     */
    private $value;

    /**
     * The field that's gonna be the text on the SelectItem
     * @var string 
     */
    private $display;

    /**
     * Initializes a new instance of the SelectList class by using the specified items for the list.
     * @param array|Collection $list
     * @param string $value The field's name that will be used in the value attribute
     * @param string $display The field's name that will be used in the display attribute
     */
    public function __construct($list, $value, $display)
    {
        $this->list = new Dictionary();

        foreach ($list as $key => $val) {
            $this->list->add($key, $val);
        }

        $this->value = $value;
        $this->display = $display;
    }

    /**
     * Gets the items in the list.
     * @return Collection
     */
    public function getItems()
    {
        if (empty($this->items)) {
            $this->items = new Collection();
            foreach ($this->list as $item => $value) {
                if (is_object($value)) {
                    $this->items->add(new SelectItem($value->{$this->display}, $value->{$this->value}));
                } else {
                    $this->items->add(new SelectItem($value, $item));
                }
            }
        }
        return $this->items;
    }

    /**
     * Sets the items in the list.
     * @param Collection $items
     */
    public function setItems($items)
    {
        $this->list = $items;
    }

    /**
     * Gets the value fild's name
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value fild's name
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the display fild's name
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Sets the display fild's name
     * @param string $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

}
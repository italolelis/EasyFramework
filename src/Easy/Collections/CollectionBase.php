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

namespace Easy\Collections;

use Easy\Collections\Enumerable;

/**
 * Provides the abstract base class for a strongly typed collection.
 */
abstract class CollectionBase extends Enumerable implements ICollection
{

    public function __construct($array = null)
    {
        if (is_array($array) || $array instanceof IteratorAggregate) {
            $this->addMultiple($array);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->GetArray());
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->array = array();
    }

    /**
     * {@inheritdoc}
     */
    public function contains($item)
    {
        return $this->itemExists($item, $this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function IsEmpty()
    {
        return $this->count() < 1;
    }

    protected function addMultiple($items)
    {
        if (!is_array($items) && !($items instanceof IteratorAggregate)) {
            throw new InvalidArgumentException(__('Items must be either a Collection or an array'));
            return;
        }
        if ($items instanceof Enumerable) {
            $array = array_values($items->GetArray());
        } else if (is_array($items)) {
            $array = $items;
        } else if ($items instanceof IteratorAggregate) {
            foreach ($items as $k => $v) {
                $array[$k] = $v;
            }
        }
        if (empty($array) == false) {
            $this->array = array_merge($this->array, $array);
        }
    }

}
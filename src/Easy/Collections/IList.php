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

/*
 * Represents a non-generic collection of objects that can be individually accessed by index.
 */
interface IList extends ICollection
{

    /**
     * Adds an item to the IList.
     * @param mixed $item The object to add to the IList.
     */
    public function add($item);

    /**
     * Adds the elements of the specified collection to the end of the IList.
     * @param ICollection|array $items The collection whose elements should be added to the end of the IList.
     */
    public function addRange($items);

    /**
     * Removes the element with the specified key from the IDictionary object.
     * @param mixed $key The key of the element to remove.
     */
    public function remove($key);

    /**
     * Inserts an item to the IList at the specified index.
     * @param int $index The zero-based index at which value should be inserted.
     * @param mixed $item The object to insert into the IList.
     */
    public function insert($index, $item);

    /**
     * Determines the index of a specific item in the IList.
     * @param mixed $item The object to locate in the IList.
     * @param int $start
     * @param int $length
     */
    public function indexOf($item, $start = null, $length = null);

    public function lastIndexOf($item, $start = null, $length = null);

    public function allIndexesOf($item);

    /**
     * Removes the IList item at the specified index.
     * @param int $index The zero-based index of the item to remove.
     */
    public function removeAt($index);

    public function elementAt($index);
}
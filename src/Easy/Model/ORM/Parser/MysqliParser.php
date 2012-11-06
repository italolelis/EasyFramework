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

namespace Easy\Model\ORM\Parser;

class MysqliParser
{

    protected $conditions;
    protected $values;
    protected static $operators = array(
        '=', '<>', '!=', '<=', '<', '>=', '>', '<=>', 'LIKE', 'REGEXP',
        '&', '\|', '^', '~', '<<', '>>'
    );
    protected static $logical = array(
        'and', 'and not', 'or', 'or not', 'xor', 'not'
    );

    public function __construct($conditions)
    {
        list($this->values, $this->conditions) = $this->evaluate($conditions);
    }

    public function conditions()
    {
        return $this->conditions;
    }

    public function values()
    {
        return $this->values;
    }

    protected function evaluate($params, $logical = 'and')
    {
        $values = $sql = array();
        if (is_array($params)) {
            foreach ($params as $k => $param) {
                if (!is_numeric($k)) {
                    if (in_array($k, self::$logical)) {
                        $result = $this->evaluate($param, $k);
                        $sql [] = '(' . $result[1] . ')';
                        $values = array_merge($values, $result[0]);
                    } else {
                        $field = $this->field($k);
                        if (is_null($field)) {
                            $sql [] = $k;
                            $values += array_values($param);
                            continue;
                        }

                        list($field, $operator) = $field;
                        if (!is_array($param)) {
                            $sql [] = $field . ' ' . $operator . $param;
                            $values [] = $param;
                        } else {
                            $repeat = rtrim(str_repeat('?,', count($param)), ',');
                            $sql [] = $field . ' IN(' . $repeat . ')';
                            $values = array_merge($values, array_values($param));
                        }
                    }
                } else {
                    $sql [] = $params;
                }
            }

            $logical = ' ' . strtoupper($logical) . ' ';
            $sql = join($logical, $sql);
        } else {
            $sql [] = $params;
        }

        return array($values, $sql);
    }

    protected function field($field)
    {
        $regex = '/^([\S]+)(?:\s?(' . join('|', self::$operators) . '))?$/';

        if (preg_match($regex, $field, $result)) {
            array_shift($result);
            if (!isset($result[1])) {
                $result[1] = '=';
            }

            return $result;
        }
    }

}
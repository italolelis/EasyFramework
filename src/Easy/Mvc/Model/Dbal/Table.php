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

namespace Easy\Mvc\Model\Dbal;

use Easy\Cache\Cache;

/**
 * Object Representation of a table
 * @since   2.0
 * @author  Ítalo Lelis de Vietro <italolelis@gmail.com>
 */
class Table
{

    /**
     * @var string The table name
     */
    protected $name = null;

    /**
     * @var string The primary key name
     */
    protected $primaryKey = false;

    /**
     * @var string The table prefix 
     */
    protected $prefix;

    /**
     * @var ISchema The schema object for this table 
     */
    protected $schema;

    /**
     *
     * @param string $tableName
     * @param ISchema $schema
     */
    public function __construct($tableName, ISchema $schema, $prefix = null)
    {
        if (empty($tableName)) {
            throw new Exceptions\DbalException('Invalid table name');
        }
        $this->name = $tableName;
        $this->schema = $schema;
        $this->prefix = $prefix;
        $this->getColumns();
    }

    public function getName()
    {
        return $this->prefix . $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Gets the columns of this table
     * @return array
     * @todo Implement a cache system
     */
    public function getColumns()
    {
        //$schema = Cache::read('columns', '_easy_model_');
        //if (empty($schema)) {
        $schema = $this->schema->getDriver()->listColumns($this->name);
        if (empty($this->primaryKey)) {
            foreach ($schema as $field => $describe) {
                if ($describe['key'] == 'PRI') {
                    $this->primaryKey = $field;
                    break;
                }
            }
        }
        //Cache::write('columns', $schema, '_easy_model_');
        //}
        return $schema;
    }

}
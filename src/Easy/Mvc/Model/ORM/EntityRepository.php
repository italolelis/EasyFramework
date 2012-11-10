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

namespace Easy\Mvc\Model\ORM;

use Easy\Core\App;
use Easy\Core\Object;
use Easy\Mvc\Model\Dbal\IDriver;
use Easy\Mvc\Model\Dbal\Schema;
use Easy\Mvc\Model\Dbal\Table;

/**
 * An EntityRepository serves as a repository for entities with generic as well as business specific methods for retrieving entities.
 * This class is designed for inheritance and users can subclass this class to write their own repositories with business-specific methods to locate entities.
 * 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class EntityRepository extends Object
{

    /**
     * @var string Schema object
     */
    protected $schema;
    protected $entityName;
    protected $mappers = array();

    public function __construct($entityName, IDriver $driver)
    {
        $this->entityName = $entityName;
        //TODO: Implementar maneira de não instanciar mapper caso não seja necessário
        if (!isset($this->mappers[$entityName])) {
            $mapperClass = App::classname($entityName, "Model/Mapping", "Mapper");
            $this->mappers[$entityName] = new $mapperClass();
        }
        $options = $driver->getConfig();
        $this->schema = new Schema($driver);
        $table = new Table($this->mappers[$entityName]->getTableName(), $this->schema, $options['prefix']);
        $this->schema->addTable($table);
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getTable()
    {
        return $this->schema->getTable($this->mappers[$this->entityName]->getTableName());
    }

    public function getMapper()
    {
        return $this->mappers[$this->entityName];
    }

    public function getEntityName()
    {
        return $this->entityName;
    }

    public function getNamespacedEntityName()
    {
        return App::classname($this->getEntityName(), "Model");
    }

}

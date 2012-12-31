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

namespace Easy\Mvc\Model\ORM\Relations;

use Easy\Collections\ICollection;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\Model\ORM\Conditions;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Mvc\Model\ORM\IMapper;
use Easy\Mvc\Model\ORM\Query;
use Easy\Utility\Hash;
use Easy\Utility\Inflector;

class Relation
{

    /**
     * @var IModel 
     */
    protected $model;

    /**
     * @var string 
     */
    protected $modelName;

    /**
     * @var IMapper 
     */
    protected static $mappers;

    /**
     * @var EntityManager 
     */
    protected
            $entityManager;

    public function __construct($model)
    {
        $this->model = $model;
        list(, $modelClass) = namespaceSplit(get_class($model));
        $this->modelName = $modelClass;
        $this->entityManager = EntityManager::getInstance();
    }

    public function buildRelations($name)
    {
        $mapper = $this->entityManager->getRepository($this->modelName)->getMapper();
        if ($mapper->hasOne() != null) {
            if ($this->buildHasOne($name, $mapper)) {
                return true;
            }
        }
        if ($mapper->hasMany() != null) {
            if ($this->buildHasMany($name, $mapper)) {
                return true;
            }
        }
        if ($mapper->belongsTo() != null) {
            if ($this->buildBelongsTo($name, $mapper)) {
                return true;
            }
        }
        if ($mapper->hasAndBelongsToMany() != null) {
            if ($this->buildHasAndBelongsToMany($name, $mapper)) {
                return true;
            }
        }
    }

    public function buildHasOne($name, IMapper $mapper)
    {
        foreach ($mapper->hasOne() as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }
            if ($assocModel === $name) {
                $primaryKey = $this->getModelPrimaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore($assocModel) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true), $options);

                if (!isset($options['conditions'])) {
                    $conditions = array($primaryKey => $this->model->{$options['foreignKey']});
                }
                $results = $this->entityManager->findOneBy($options['className'], $conditions);
                $this->model->{$assocModel} = $results;

                return true;
            }
        }
    }

    public function buildHasMany($name, IMapper $mapper)
    {
        foreach ($mapper->hasMany() as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }
            if ($assocModel === $name) {
                $primaryKey = $this->getModelPrimaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true), $options);
                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$primaryKey});
                }

                $query = new Query();
                $query->where(new Conditions($options['conditions']));

                $results = $this->entityManager->findByQuery($options['className'], $query);
                $this->createModelProperty($assocModel, $results);

                return true;
            }
        }
    }

    public function buildBelongsTo($name, $mapper)
    {
        foreach ($mapper->belongsTo() as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }

            if ($assocModel === $name) {
                $primaryKey = $this->getModelPrimaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true), $options);

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$primaryKey});
                }

                $query = new Query();
                $query->where(new Conditions($options['conditions']));

                $results = $this->entityManager->findByQuery($options['className'], $query);
                $this->createModelProperty($assocModel, $results);

                return true;
            }
        }
    }

    public function buildHasAndBelongsToMany($name, $mapper)
    {
        foreach ($mapper->hasAndBelongsToMany() as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }

            if ($assocModel === $name) {
                $primaryKey = $this->getModelPrimaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true), $options);
                if (!isset($options['joinTable'])) {
                    $options['joinTable'] = Inflector::underscore(get_class($this->model) . "_" . $options["className"]);
                }

                if (!isset($options['associationForeignKey'])) {
                    $options['associationForeignKey'] = Inflector::underscore($options ["className"] . "_" . $this->model->{$primaryKey});
                }

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$primaryKey});
                }

                $query = new Query();
                $query->where(new Conditions($options['conditions']));
                $results = $this->entityManager->findByQuery($options['className'], $query);

                $this->createModelProperty($assocModel, $results);

                return true;
            }
        }
    }

    private function createModelProperty($property, ICollection $collection)
    {
        $results = $collection->GetArray();
        $this->model->{$property} = new RelationCollection($results);
    }

    private function getModelPrimaryKey()
    {
        return $this->entityManager->getRepository($this->modelName)->getTable()->getPrimaryKey();
    }

}
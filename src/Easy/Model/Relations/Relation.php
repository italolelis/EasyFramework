<?php

namespace Easy\Model\Relations;

use Easy\Collections\Collection;
use Easy\Core\App;
use Easy\Core\Object;
use Easy\Model\Conditions;
use Easy\Model\ConnectionManager;
use Easy\Model\EntityManager;
use Easy\Model\FindMethod;
use Easy\Model\IMapper;
use Easy\Model\Query;
use Easy\Model\Table;
use Easy\Utility\Hash;
use Easy\Utility\Inflector;

class Relation extends Object
{

    protected $model;
    protected $modelName;

    /**
     * The Mapper Object
     * @var IMapper 
     */
    protected static $mappers;
    protected $entityManager;

    public function __construct($model)
    {
        $this->model = $model;
        list(, $modelClass) = namespaceSplit(get_class($model));
        $this->modelName = $modelClass;
        $this->entityManager = new EntityManager();
        if (!isset(static::$mappers[$model])) {
            $mapperClass = App::classname($this->modelName, "Model/Mapping", "Mapper");
            static::$mappers[$this->modelName] = new $mapperClass();
        }
    }

    public function buildRelations($name)
    {
        $mapper = static::$mappers[$this->modelName];
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
                $primaryKey = $this->getTable($mapper->getTable())->primaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore($assocModel) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($primaryKey => $this->model->{$options['foreignKey']});
                }
                $query = new Query();
                $query->where(new Conditions($options['conditions']));

                $this->model->{$assocModel} = $this->entityManager->find($options['className'], $query);
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
                $primaryKey = $this->getTable($mapper->getTable())->primaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);
                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$primaryKey});
                }

                $query = new Query();
                $query->where(new Conditions($options['conditions']));

                $this->model->{$assocModel} = $this->entityManager->find($options['className'], $query, FindMethod::ALL);
                return true;
            }
        }
    }

    public function buildBelongsTo($name)
    {
        foreach ($this->belongsTo as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }

            if ($assocModel === $name) {
                $primaryKey = $this->getTable($name)->primaryKey();

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$primaryKey});
                }

                $query = new Query();
                $query->where(new Conditions($options['conditions']));

                $this->model->{$assocModel} = $this->entityManager->find($options['className'], $query, FindMethod::ALL);
                return true;
            }
        }
    }

    public function buildHasAndBelongsToMany($name)
    {
        foreach ($this->hasAndBelongsToMany as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }

            if ($assocModel === $name) {
                $primaryKey = $this->getTable($name)->primaryKey();
                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);

                if (!isset($options['joinTable'])) {
                    $options['joinTable'] = Inflector::underscore(get_class($this->model) . "_" . $options["className"]);
                }

                if (!isset($options['associationForeignKey'])) {
                    $options['associationForeignKey'] = Inflector::underscore($options["className"] . "_" . $this->model->{$primaryKey});
                }

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$primaryKey});
                }

                $query = new Query();
                $query->where(new Conditions($options['conditions']));

                $result = $this->entityManager->find($options['joinTable'], $query, FindMethod::ALL);

                if ($result) {
                    $models = new Collection();
                    $models->AddRange($result);
                }

                $this->model->{$assocModel} = $models;
                return true;
            }
        }
    }

    private function getTable($name)
    {
        $connection = ConnectionManager::getDriver('default');
        return new Table($connection, $name);
    }

}
<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM\Relations;

use Easy\Collections\CollectionInterface;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\Model\ORM\Conditions;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Mvc\Model\ORM\IMapper;
use Easy\Mvc\Model\ORM\Query;

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

                $options = array_merge(array(
                    'className' => $assocModel,
                    'foreignKey' => static::underscore($assocModel) . "_" . $primaryKey,
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

                $options = array_merge(array(
                    'className' => $assocModel,
                    'foreignKey' => static::underscore(get_class($this->model)) . "_" . $primaryKey,
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

                $options = array_merge(array(
                    'className' => $assocModel,
                    'foreignKey' => static::underscore(get_class($this->model)) . "_" . $primaryKey,
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

                $options = array_merge(array(
                    'className' => $assocModel,
                    'foreignKey' => static::underscore(get_class($this->model)) . "_" . $primaryKey,
                    'fields' => null,
                    'dependent' => true), $options);
                if (!isset($options['joinTable'])) {
                    $options['joinTable'] = static::underscore(get_class($this->model) . "_" . $options["className"]);
                }

                if (!isset($options['associationForeignKey'])) {
                    $options['associationForeignKey'] = static::underscore($options ["className"] . "_" . $this->model->{$primaryKey});
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

    private function createModelProperty($property, CollectionInterface $collection)
    {
        $results = $collection->GetArray();
        $this->model->{$property} = new RelationCollection($results);
    }

    private function getModelPrimaryKey()
    {
        return $this->entityManager->getRepository($this->modelName)->getTable()->getPrimaryKey();
    }

    public static function underscore($camelCasedWord)
    {
        $result = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
        return $result;
    }

}
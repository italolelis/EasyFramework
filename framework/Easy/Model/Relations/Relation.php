<?php

namespace Easy\Model\Relations;

use Easy\Core\Object;
use Easy\Core\App;
use Easy\Collections\Collection;
use Easy\Model\Metadata\RelationsMetadata;
use Easy\Model\FindMethod;
use Easy\Utility\ClassRegistry;
use Easy\Utility\Hash;
use Easy\Utility\Inflector;

class Relation extends Object
{

    protected $model;
    protected $primaryKey;
    protected $hasOne;
    protected $hasMany;
    protected $belongsTo;
    protected $hasAndBelongsToMany = array();

    public function __construct($model)
    {
        $this->model = $model;
        $this->primaryKey = $this->model->getEntityManager()->primaryKey();
        $this->getMetadata($model);
    }

    public function getMetadata($model)
    {
        $metadata = new RelationsMetadata();
        $this->hasOne = $metadata->getHasOne($model);
        $this->hasMany = $metadata->getHasMany($model);
        $this->belongsTo = $metadata->getBelongsTo($model);
        $this->hasAndBelongsToMany = $metadata->getHasAndBelongsToMany($model);
    }

    public function buildRelations($name)
    {
        //try {
        if (!empty($this->hasOne)) {
            if (is_string($this->hasOne)) {
                $this->hasOne = array($this->hasOne => array());
            }
            if ($this->buildHasOne($name)) {
                return true;
            }
        }
        if (!empty($this->hasMany)) {
            if (is_string($this->hasMany)) {
                $this->hasMany = array($this->hasMany => array());
            }
            if ($this->buildHasMany($name)) {
                return true;
            }
        }
        if (!empty($this->belongsTo)) {
            if (is_string($this->belongsTo)) {
                $this->belongsTo = array($this->belongsTo => array());
            }
            if ($this->buildBelongsTo($name)) {
                return true;
            }
        }
        if (!empty($this->hasAndBelongsToMany)) {
            if (is_string($this->hasAndBelongsToMany)) {
                $this->hasAndBelongsToMany = array($this->hasAndBelongsToMany => array());
            }

            if ($this->buildHasAndBelongsToMany($name)) {
                return true;
            }
        }
        return false;
        //} catch (Exception $exc) {
        //    return false;
        //}
    }

    public function buildHasOne($name)
    {
        foreach ($this->hasOne as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }
            if ($assocModel === $name) {
                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore($assocModel) . "_" . $this->primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);
                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($this->primaryKey => $this->model->{$options['foreignKey']});
                }

                $class = $this->loadAssociatedModel($options['className']);
                $this->model->{$assocModel} = $class->getEntityManager()->find($options);
                return true;
            }
        }
    }

    public function buildHasMany($name)
    {
        foreach ($this->hasMany as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }
            if ($assocModel === $name) {

                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $this->primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);
                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$this->primaryKey});
                }
                $class = $this->loadAssociatedModel($options['className']);
                $this->model->{$assocModel} = $class->getEntityManager()->find($options, FindMethod::ALL);
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
                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $this->primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$this->primaryKey});
                }

                $class = $this->loadAssociatedModel($options['className']);
                $this->model->{$assocModel} = $class->getEntityManager()->find($options, FindMethod::ALL);
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
                $options = Hash::merge(array(
                            'className' => $assocModel,
                            'foreignKey' => Inflector::underscore(get_class($this->model)) . "_" . $this->primaryKey,
                            'fields' => null,
                            'dependent' => true
                                ), $options);

                if (!isset($options['joinTable'])) {
                    $options['joinTable'] = Inflector::underscore(get_class($this->model) . "_" . $options["className"]);
                }

                if (!isset($options['associationForeignKey'])) {
                    $options['associationForeignKey'] = Inflector::underscore($options["className"] . "_" . $this->model->{$this->primaryKey});
                }

                $joinModel = ClassRegistry::load($assocModel);
                $joinModel->table = $options['joinTable'];

                if (!isset($options['conditions'])) {
                    $options['conditions'] = array($options['foreignKey'] => $this->model->{$this->primaryKey});
                }

                $result = $joinModel->getEntityManager()->find($options, FindMethod::ALL);

                $models = new Collection();
                if ($result) {
                    foreach ($result as $r) {
                        $models->Add($r);
                    }
                }
                $this->model->{$assocModel} = $models;
                return true;
            }
        }
    }

    public function loadAssociatedModel($assocModel)
    {
        //App::uses($assocModel, 'Model');
        $assocModel = App::classname($assocModel, "Model");
        return new $assocModel();
    }

}
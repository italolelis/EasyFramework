<?php

class Relation extends Object
{

    protected $model;
    protected $primaryKey;

    public function __construct($model)
    {
        $this->model = $model;
        $this->primaryKey = $this->model->getEntityManager()->primaryKey();
    }

    public function buildRelations($name)
    {

        //try {
        if (!empty($this->model->hasOne)) {
            if (is_string($this->model->hasOne)) {
                $this->model->hasOne = array($this->model->hasOne => array());
            }
            if ($this->buildHasOne($name)) {
                return true;
            }
        }
        if (!empty($this->model->hasMany)) {
            if (is_string($this->model->hasMany)) {
                $this->model->hasMany = array($this->model->hasMany => array());
            }
            if ($this->buildHasMany($name)) {
                return true;
            }
        }
        if (!empty($this->model->belongsTo)) {
            if (is_string($this->model->belongsTo)) {
                $this->model->belongsTo = array($this->model->belongsTo => array());
            }
            if ($this->buildBelongsTo($name)) {
                return true;
            }
        }
        if (!empty($this->model->hasAndBelongsToMany)) {
            if (is_string($this->model->hasAndBelongsToMany)) {
                $this->model->hasAndBelongsToMany = array($this->model->hasAndBelongsToMany => array());
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
        foreach ($this->model->hasOne as $assocModel => $options) {
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
        foreach ($this->model->hasMany as $assocModel => $options) {
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
        foreach ($this->model->belongsTo as $assocModel => $options) {
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
        foreach ($this->model->hasAndBelongsToMany as $assocModel => $options) {
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
                $models = array();
                if ($result) {
                    foreach ($result as $r) {
                        $r->hasOne = array(
                            $options['className'] => array(
                                "className" => $options['className'],
                                "foreignKey" => $options['associationForeignKey']
                            )
                        );
                        $models[] = $r;
                    }
                }
                $this->model->{$assocModel} = $models;
                return true;
            }
        }
    }

    public function loadAssociatedModel($assocModel)
    {
        App::uses($assocModel, 'Model');
        return new $assocModel();
    }

}
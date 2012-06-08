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

    public function buildRelations()
    {
        try {
            if (!empty($this->model->hasOne)) {
                $this->buildHasOne();
            }
            if (!empty($this->model->hasMany)) {
                $this->buildHasMany();
            }
            if (!empty($this->model->belongsTo)) {
                $this->buildBelongsTo();
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function buildHasOne()
    {
        if (is_string($this->model->hasOne)) {
            $this->model->hasOne = array($this->model->hasOne => array());
        }
        foreach ($this->model->hasOne as $assocModel => $options) {
            if (is_string($options)) {
                $assocModel = $options;
                $options = array();
            }
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
        }
    }

    public function buildHasMany()
    {
        if (is_string($this->model->hasMany)) {
            $this->model->hasMany = array($this->model->hasMany => array());
        }
        foreach ($this->model->hasMany as $assocModel => $options) {
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

            $this->model->{$assocModel} = $class->getEntityManager()->find($options, EntityManager::FIND_ALL);
        }
    }

    public function buildBelongsTo()
    {
        if (is_string($this->model->belongsTo)) {
            $this->model->belongsTo = array($this->model->belongsTo => array());
        }
        foreach ($this->model->belongsTo as $assocModel => $options) {
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
            $this->model->{$assocModel} = $class->getEntityManager()->find($options, EntityManager::FIND_ALL);
        }
    }

    public function loadAssociatedModel($assocModel)
    {
        App::uses($assocModel, 'Model');
        return new $assocModel();
    }

}
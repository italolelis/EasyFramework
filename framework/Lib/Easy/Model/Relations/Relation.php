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
            $this->buildHasOne($name);
        }
            //Debugger::dump($name);
        if (!empty($this->model->hasMany)) {
            if (is_string($this->model->hasMany)) {
                $this->model->hasMany = array($this->model->hasMany => array());
            }
            $this->buildHasMany($name);
        }
        if (!empty($this->model->belongsTo)) {
            if (is_string($this->model->belongsTo)) {
                $this->model->belongsTo = array($this->model->belongsTo => array());
            }
            $this->buildBelongsTo($name);
        }
            return true;
//        } catch (Exception $exc) {
//            return false;
//        }
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
                return $this->model->{$assocModel} = $class->getEntityManager()->find($options);
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

                return $this->model->{$assocModel} = $class->getEntityManager()->find($options, EntityManager::FIND_ALL);
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
                $this->model->{$assocModel} = $class->getEntityManager()->find($options, EntityManager::FIND_ALL);
            }
        }
    }

    public function loadAssociatedModel($assocModel)
    {
        App::uses($assocModel, 'Model');
        return new $assocModel();
    }

}
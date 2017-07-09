<?php

namespace yii\modelcollection;


use yii\base\Arrayable;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;

class ModelCollection extends Collection
{
    /**
     * @var ActiveQuery|null the query that returned this collection.
     * May be`null` if the collection has not been created by a query.
     */
    public $query;

    private $_models;

    /**
     * Collection constructor.
     * @param array $data
     * @param array $config
     */
    public function __construct($models = [], $config = [])
    {
        $this->_models = $models;
        parent::__construct([], $config);
    }

    /**
     * Lazy evaluation of models, if this collection has been created from a query.
     */
    public function getData()
    {
        if ($this->_models === null) {
            if ($this->query === null) {
                throw new InvalidCallException('This collection was not created from a query.');
            }
            $this->_models = $this->query->all();
            $this->setData($this->_models);
        }
        return parent::getData();
    }

    /**
     * @return array|BaseActiveRecord[]|ActiveRecordInterface[]|Arrayable[] models contained in this collection.
     */
    public function getModels()
    {
        return $this->getData();
    }

    // TODO relational operations like link() and unlink() sync()
    // https://github.com/yiisoft/yii2/pull/12304#issuecomment-242339800
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242346294

    // TODO addToRelation() by checking if query is a relation
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-241505294


    // https://github.com/yiisoft/yii2/issues/12743
    public function findWith($with)
    {
        if (!$this->query) {
            throw new InvalidCallException('This collection was not created from a query, so findWith() is not possible.');
        }
        $this->ensureModels();
        $this->query->findWith(['colors'], $this->_models);
        return $this;
    }


    // AR specific stuff

    /**
     * https://github.com/yiisoft/yii2/issues/13921
     *
     * TODO add transaction support
     */
    public function deleteAll()
    {
        $this->ensureModels();
        foreach($this->_models as $model) {
            $model->delete();
        }
    }

    public function scenario($scenario)
    {
        $this->ensureModels();
        foreach($this->_models as $model) {
            $model->scenario = $scenario;
        }
        return $this;
    }

    /**
     * https://github.com/yiisoft/yii2/issues/13921
     *
     * TODO add transaction support
     */
    public function updateAll($attributes, $safeOnly = true, $runValidation = true)
    {
        $this->ensureModels();
        foreach($this->_models as $model) {
            $model->setAttributes($attributes, $safeOnly);
            $model->update($runValidation, array_keys($attributes));
        }
        return $this;
    }

    public function insertAll()
    {
        // TODO could be a batch insert
        return $this;
    }

    public function saveAll($runValidation = true, $attributeNames = null)
    {
        $this->ensureModels();
        foreach($this->_models as $model) {
            $model->update($runValidation, $attributeNames);
        }
        return $this;
    }

    /**
     * https://github.com/yiisoft/yii2/issues/10806#issuecomment-242119472
     *
     * @return bool
     */
    public function validateAll()
    {
        $this->ensureModels();
        $success = true;
        foreach($this->_models as $model) {
            if (!$model->validate()) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * @param array $fields
     * @param array $expand
     * @param bool $recursive
     * @return Collection|static
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return $this->map(function($model) use ($fields, $expand, $recursive) {
            /** @var $model Arrayable */
            return $model->toArray();
        });
    }

    public function toJson($options = 320)
    {
        return Json::encode($this->toArray()->_models, $options);
    }

}

<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\modelcollection;

use yii\base\Arrayable;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

// TODO take a look at https://github.com/nikic/iter
// TODO take a look at https://github.com/Athari/YaLinqo

/**
 * Class Collection
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Collection extends Component implements \ArrayAccess, \Iterator
{
    // TODO implement iterator and array access

    /**
     * @var array|BaseActiveRecord[]|ActiveRecordInterface[]|Arrayable[]
     */
    private $_models;
    private $_config;

    /**
     * @var ActiveQuery
     */
    public $query;

    public function __construct($models, $config = [])
    {
        $this->_models = $models;
        parent::__construct($config);
    }

    private function ensureModels()
    {
        if ($this->_models === null) {
            if ($this->query === null) {
                throw new InvalidCallException('This collection was not created from a query.');
            }
            $this->_models = $this->query->all();
        }
    }

    // https://laravel.com/docs/5.1/collections

    // TODO relational operations like link() and unlink() sync()
    // https://github.com/yiisoft/yii2/pull/12304#issuecomment-242339800
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242346294

    // TODO addToRelation() by checking if query is a relation
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-241505294

    // TODO allow extension
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242117369
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242150877


    // TODO add, contains, remove, replace
    // https://github.com/yiisoft/yii2/issues/9763


    // basic collection operations


    public function map($callable)
    {
        $this->ensureModels();
        return new static(array_map($callable, $this->_models), $this->_config);
    }

    public function filter($callable)
    {
        $this->ensureModels();
        return new static(array_filter($this->_models, $callable, ARRAY_FILTER_USE_BOTH), $this->_config);
    }

    public function reduce($callable, $initialValue = null)
    {
        $this->ensureModels();
        return array_reduce($this->_models, $callable, $initialValue);
    }

    public function sort($field)
    {
        $this->ensureModels();
        // TODO
    }

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

    public function convert($from, $to, $group)
    {
        $this->ensureModels();
        return new static(ArrayHelper::map($this->_models, $from, $to, $group));
    }

    public function sum($field)
    {
        return $this->reduce(function($carry, $model) use ($field) {
            return $carry + ArrayHelper::getValue($model, $field, 0);
        }, 0);
    }

    public function max($field)
    {
        return $this->reduce(function($carry, $model) use ($field) {
            $value = ArrayHelper::getValue($model, $field, 0);
            if ($carry === null) {
                return $value;
            } else {
                return $value > $carry ? $value : $carry;
            }
        });
    }

    public function min($field)
    {
        return $this->reduce(function($carry, $model) use ($field) {
            $value = ArrayHelper::getValue($model, $field, 0);
            if ($carry === null) {
                return $value;
            } else {
                return $value < $carry ? $value : $carry;
            }
        });
    }

    public function count()
    {
        $this->ensureModels();
        return count($this->_models);
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

    // ArrayAccess methods

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        $this->ensureModels();
        return isset($this->_models[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $this->ensureModels();
        return $this->_models[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new InvalidCallException('Collection is readonly.');
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new InvalidCallException('Collection is readonly.');
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $this->ensureModels();
        return current($this->_models);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->ensureModels();
        next($this->_models);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        $this->ensureModels();
        return key($this->_models);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->ensureModels();
        return current($this->_models) !== false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->ensureModels();
        reset($this->_models);
    }
}

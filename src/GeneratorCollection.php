<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\collection;

use yii\base\Arrayable;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;
use yii\db\BatchQueryResult;
use yii\helpers\ArrayHelper;

// TODO take a look at https://github.com/nikic/iter
// TODO take a look at https://github.com/Athari/YaLinqo

// TODO lazy evaluation of collection

/**
 * GeneratorCollection
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 1.0
 */
class GeneratorCollection extends Component implements \Iterator
{
    /**
     * @var ActiveQuery
     */
    public $query;

    /**
     * @var int
     */
    public $batchSize;

    /**
     * @var BatchQueryResult
     */
    private $_batch;

    public function __construct($batchSize, $config = [])
    {
        $this->batchSize = $batchSize;
        parent::__construct($config);
    }

    /**
     * @return BatchQueryResult|BaseActiveRecord[]
     */
    private function queryEach()
    {
        if ($this->query === null) {
            throw new InvalidCallException('This collection was not created from a query.');
        }
        return $this->query->each($this->batchSize); // TODO inject DB
    }

    private function ensureBatch()
    {
        if ($this->_batch === null) {
            $this->_batch = $this->queryEach();
        }
    }

    // basic collection operations


    public function map($callable)
    {
        foreach ($this->queryEach() as $key => $value) {
            yield $key => $callable($value, $key);
        }
    }

    public function filter($callable)
    {
        foreach ($this->queryEach() as $key => $value) {
            if ($callable($value, $key)) {
                yield $key => $value;
            }
        }
    }

    public function flatMap($callable)
    {
        foreach ($this->queryEach() as $key => $value) {
            foreach ($callable($value, $key) as $k => $v) {
                yield $k => $v;
            }
        }
    }

    public function indexBy($index)
    {
        foreach ($this->queryEach() as $key => $model) {
            yield ArrayHelper::getValue($model, $index) => $model;
        }
    }

    public function reduce($callable, $initialValue = null)
    {
        $result = $initialValue;
        foreach ($this->queryEach() as $key => $value) {
            $result = $callable($result, $value);
        }
        return $result;
    }

    public function values()
    {
        foreach ($this->queryEach() as $key => $value) {
            yield $value;
        }
    }

    public function keys()
    {
        foreach ($this->queryEach() as $key => $value) {
            yield $key;
        }
    }

    public function sum($field)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            return $carry + ArrayHelper::getValue($model, $field, 0);
        }, 0);
    }

    public function max($field)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            $value = ArrayHelper::getValue($model, $field, 0);
            if ($carry === null) {
                return $value;
            }
            return $value > $carry ? $value : $carry;
        });
    }

    public function min($field)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            $value = ArrayHelper::getValue($model, $field, 0);
            if ($carry === null) {
                return $value;
            }
            return $value < $carry ? $value : $carry;
        });
    }

    public function count()
    {
        return $this->reduce(function ($carry) {
            return $carry + 1;
        }, 0);
    }

    // AR specific stuff

    /**
     * https://github.com/yiisoft/yii2/issues/13921
     *
     * TODO add transaction support
     */
    public function deleteAll()
    {
        foreach ($this->queryEach() as $model) {
            $model->delete();
        }
    }

    /**
     * https://github.com/yiisoft/yii2/issues/13921
     *
     * TODO add transaction support
     */
    public function updateAll($attributes, $safeOnly = true, $runValidation = true)
    {
        foreach ($this->queryEach() as $model) {
            $model->setAttributes($attributes, $safeOnly);
            $model->update($runValidation, \array_keys($attributes));
        }
        return $this;
    }

    /**
     * @param array $fields
     * @param array $expand
     * @param bool $recursive
     * @return Collection|static
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return $this->map(function ($model) use ($fields, $expand, $recursive) {
            /** @var $model Arrayable */
            return $model->toArray($fields, $expand, $recursive);
        });
    }

    // Iterator methods

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $this->ensureBatch();
        return $this->_batch->current();
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->ensureBatch();
        $this->_batch->next();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        $this->ensureBatch();
        return $this->_batch->key();
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->ensureBatch();
        return $this->_batch->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->ensureBatch();
        $this->_batch->rewind();
    }
}

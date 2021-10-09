<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\collection;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use Closure;
use yii\base\ArrayAccessTrait;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidArgumentException;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Collection is a container for a set of items.
 *
 * It provides methods for transforming and filtering the items as well as sorting methods, which can be applied
 * using a chained interface. All these operations will return a new collection containing the modified data
 * keeping the original collection as it was as long as containing objects state is not changed.
 *
 * ```php
 * $collection = new Collection([1, 2, 3]);
 * echo $collection->map(function($i) { // [2, 3, 4]
 *     return $i + 1;
 * })->filter(function($i) { // [2, 3]
 *     return $i < 4;
 * })->sum(); // 5
 * ```
 *
 * The collection use [[ArrayAccessTrait]], so you can access it in the same way you use a PHP array.
 * A collection however is read-only, you can not manipulate single items.
 *
 * ```php
 * $collection = new Collection([1, 2, 3]);
 * echo $collection[1]; // 2
 * foreach($collection as $item) {
 *     echo $item . ' ';
 * } // will print 1 2 3
 * ```
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 1.0
 */
class Collection extends Component implements ArrayAccess, IteratorAgregate, Countable
{
    use ArrayAccessTrait;

    /**
     * @var array the collection data
     */
    private $data;

    /**
     * Creates new collection.
     *
     * @param array $data the collection data.
     * @param array $config the configuration.
     */
    public function __construct(array $data, array $config = [])
    {
        $this->setData($data);
        parent::__construct($config);
    }

    /**
     * Returns the data contained in this collection.
     *
     * @return array the reference to collection data.
     */
    public function &getData()
    {
        return $this->data;
    }

    /**
     * Sets the collection data.
     *
     * @param array $data the collection data.
     * @return void
     */
    protected function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool a value indicating whether the collection is empty.
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Apply callback to all items in the collection.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     * @param callable $callable the callback function to apply, syntax: `function (mixed $value): mixed`.
     * @return static a new collection with items returned from the callback.
     */
    public function map($callable)
    {
        return new static(\array_map($callable, $this->getData()));
    }

    /**
     * Apply callback to all items and return multiple results.
     *
     * Apply callback to all items in the collection and return a new collection containing all items
     * returned by the callback.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     * @param callable $callable the callback function to apply, syntax: `function (mixed $value): mixed`.
     * @return static a new collection with items returned from the callback.
     */
    public function flatMap($callable)
    {
        return $this->map($callable)->collapse();
    }

    /**
     * Merges all sub arrays into one array.
     *
     * For example:
     *
     * ```php
     * $collection = new Collection([[1,2], [3,4], [5,6]]);
     * $collapsed = $collection->collapse(); // [1,2,3,4,5,6];
     * ```
     *
     * This method can only be called on a collection which contains arrays.
     * The original collection will not be changed, a new collection with modified data is returned.
     * @return static a new collection containing the collapsed array result.
     */
    public function collapse()
    {
        return new static($this->reduce('\array_merge', []));
    }

    /**
     * Filter items from the collection.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     * @param callable $callable the callback function to decide which items to remove. Signature: `function($model, $key)`.
     * Should return `true` to keep an item and return `false` to remove them.
     * @return static a new collection containing the filtered items.
     */
    public function filter($callable)
    {
        return new static(\array_filter($this->getData(), $callable, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Apply reduce operation to items from the collection.
     * @param callable $callable the callback function to compute the reduce value. Signature: `function($carry, $model)`.
     * @param mixed $initialValue initial value to pass to the callback on first item.
     * @return mixed the result of the reduce operation.
     */
    public function reduce($callable, $initialValue = null)
    {
        return \array_reduce($this->getData(), $callable, $initialValue);
    }

    /**
     * Calculate the sum of a field of the models in the collection.
     * @param string|Closure|array $field the name of the field to calculate.
     * This will be passed to [[ArrayHelper::getValue()]].
     * @return mixed the calculated sum.
     */
    public function sum($field = null)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            return $carry + ($field === null ? $model : ArrayHelper::getValue($model, $field, 0));
        }, 0);
    }

    /**
     * Calculate the maximum value of a field of the models in the collection
     * @param string|Closure|array $field the name of the field to calculate.
     * This will be passed to [[ArrayHelper::getValue()]].
     * @return mixed the calculated maximum value. 0 if the collection is empty.
     */
    public function max($field = null)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            $value = ($field === null ? $model : ArrayHelper::getValue($model, $field, 0));
            if ($carry === null) {
                return $value;
            }
            return $value > $carry ? $value : $carry;
        });
    }

    /**
     * Calculate the minimum value of a field of the models in the collection
     * @param string|Closure|array $field the name of the field to calculate.
     * This will be passed to [[ArrayHelper::getValue()]].
     * @return mixed the calculated minimum value. 0 if the collection is empty.
     */
    public function min($field = null)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            $value = ($field === null ? $model : ArrayHelper::getValue($model, $field, 0));
            if ($carry === null) {
                return $value;
            }
            return $value < $carry ? $value : $carry;
        });
    }

    /**
     * Sort collection data by value.
     *
     * If the collection values are not scalar types, use [[sortBy()]] instead.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     * @param int $direction sort direction, either `SORT_ASC` or `SORT_DESC`.
     * @param int $sortFlag type of comparison, either `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`,
     * `SORT_LOCALE_STRING`, `SORT_NATURAL` or `SORT_FLAG_CASE`.
     * See [the PHP manual](http://php.net/manual/en/function.sort.php#refsect1-function.sort-parameters)
     * for details.
     * @return static a new collection containing the sorted items.
     * @see http://php.net/manual/en/function.asort.php
     * @see http://php.net/manual/en/function.arsort.php
     */
    public function sort($direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $data = $this->getData();
        if ($direction === SORT_ASC) {
            \asort($data, $sortFlag);
        } else {
            \arsort($data, $sortFlag);
        }
        return new static($data);
    }

    /**
     * Sort collection data by key.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     * @param int $direction sort direction, either `SORT_ASC` or `SORT_DESC`.
     * @param int $sortFlag type of comparison, either `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`,
     * `SORT_LOCALE_STRING`, `SORT_NATURAL` or `SORT_FLAG_CASE`.
     * See [the PHP manual](http://php.net/manual/en/function.sort.php#refsect1-function.sort-parameters)
     * for details.
     * @return static a new collection containing the sorted items.
     * @see http://php.net/manual/en/function.ksort.php
     * @see http://php.net/manual/en/function.krsort.php
     */
    public function sortByKey($direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $data = $this->getData();
        if ($direction === SORT_ASC) {
            \ksort($data, $sortFlag);
        } else {
            \krsort($data, $sortFlag);
        }
        return new static($data);
    }

    /**
     * Sort collection data by value using natural sort comparsion.
     *
     * If the collection values are not scalar types, use [[sortBy()]] instead.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     * @param bool $caseSensitive whether comparison should be done in a case-sensitive manner. Defaults to `false`.
     * @return static a new collection containing the sorted items.
     * @see http://php.net/manual/en/function.natsort.php
     * @see http://php.net/manual/en/function.natcasesort.php
     */
    public function sortNatural($caseSensitive = false)
    {
        $data = $this->getData();
        if ($caseSensitive) {
            \natsort($data);
        } else {
            \natcasesort($data);
        }
        return new static($data);
    }

    /**
     * Sort collection data by one or multiple values.
     *
     * This method uses [[ArrayHelper::multisort()]] on the collection data.
     *
     * Note that keys will not be preserved by this method.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     * @param string|Closure|array $key the key(s) to be sorted by. This refers to a key name of the sub-array
     * elements, a property name of the objects, or an anonymous function returning the values for comparison
     * purpose. The anonymous function signature should be: `function($item)`.
     * To sort by multiple keys, provide an array of keys here.
     * @param int|array $direction the sorting direction. It can be either `SORT_ASC` or `SORT_DESC`.
     * When sorting by multiple keys with different sorting directions, use an array of sorting directions.
     * @param int|array $sortFlag the PHP sort flag. Valid values include
     * `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`, `SORT_LOCALE_STRING`, `SORT_NATURAL` and `SORT_FLAG_CASE`.
     * Please refer to the [PHP manual](http://php.net/manual/en/function.sort.php)
     * for more details. When sorting by multiple keys with different sort flags, use an array of sort flags.
     * @return static a new collection containing the sorted items.
     * @throws InvalidArgumentException if the $direction or $sortFlag parameters do not have
     * correct number of elements as that of $key.
     * @see ArrayHelper::multisort()
     */
    public function sortBy($key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $data = $this->getData();
        ArrayHelper::multisort($data, $key, $direction, $sortFlag);
        return new static($data);
    }

    /**
     * Reverse the order of items.
     *
     * The original collection will not be changed, a new collection with items in reverse order is returned.
     * @return static a new collection containing the items in reverse order.
     */
    public function reverse()
    {
        return new static(\array_reverse($this->getData(), true));
    }

    /**
     * Return items without keys.
     * @return static a new collection containing the values of this collections data.
     */
    public function values()
    {
        return new static(\array_values($this->getData()));
    }

    /**
     * Return keys of all collection items.
     * @return static a new collection containing the keys of this collections data.
     */
    public function keys()
    {
        return new static(\array_keys($this->getData()));
    }

    /**
     * Flip keys and values of all collection items.
     * @return static a new collection containing the data of this collections flipped by key and value.
     */
    public function flip()
    {
        return new static(\array_flip($this->getData()));
    }

    /**
     * Merge two collections or this collection with an array.
     *
     * Data in this collection will be overwritten if non-integer keys exist in the merged collection.
     *
     * The original collection will not be changed, a new collection with items in reverse order is returned.
     * @param array|Collection $collection the collection or array to merge with.
     * @return static a new collection containing the merged data.
     */
    public function merge($collection)
    {
        if ($collection instanceof Collection) {
            return new static(\array_merge($this->getData(), $collection->getData()));
        } elseif (\is_array($collection)) {
            return new static(\array_merge($this->getData(), $collection));
        }
        throw new InvalidArgumentException('Collection can only be merged with an array or other collections.');
    }

    /**
     * Convert collection data by selecting a new key and a new value for each item.
     *
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     * The `$from` and `$to` parameters specify the key names or property names to set up the map.
     *
     * The original collection will not be changed, a new collection with newly mapped data is returned.
     * @param string|Closure $from the field of the item to use as the key of the created map.
     * This can be a closure that returns such a value.
     * @param string|Closure $to the field of the item to use as the value of the created map.
     * This can be a closure that returns such a value.
     * @return static a new collection containing the mapped data.
     * @see ArrayHelper::map()
     */
    public function remap($from, $to)
    {
        return new static(ArrayHelper::map($this->getData(), $from, $to));
    }

    /**
     * Assign a new key to each item in the collection.
     *
     * The original collection will not be changed, a new collection with newly mapped data is returned.
     * @param string|Closure $key the field of the item to use as the new key.
     * This can be a closure that returns such a value.
     * @return static a new collection containing the newly index data.
     * @see ArrayHelper::map()
     */
    public function indexBy($key)
    {
        return $this->remap($key, function ($model) {
            return $model;
        });
    }

    /**
     * Group items by a specified value.
     *
     * The original collection will not be changed, a new collection with grouped data is returned.
     * @param string|Closure $groupField the field of the item to use as the group value.
     * This can be a closure that returns such a value.
     * @param bool $preserveKeys whether to preserve item keys in the groups. Defaults to `true`.
     * @return static a new collection containing the grouped data.
     * @see ArrayHelper::map()
     */
    public function groupBy($groupField, $preserveKeys = true)
    {
        $result = [];
        if ($preserveKeys) {
            foreach ($this->getData() as $key => $element) {
                $result[ArrayHelper::getValue($element, $groupField)][$key] = $element;
            }
        } else {
            foreach ($this->getData() as $key => $element) {
                $result[ArrayHelper::getValue($element, $groupField)][] = $element;
            }
        }
        return new static($result);
    }

    /**
     * Check whether the collection contains a specific item.
     * @param mixed|Closure $item the item to search for. You may also pass a closure that returns a boolean.
     * The closure will be called on each item and in case it returns `true`, the item will be considered to
     * be found. In case a closure is passed, `$strict` parameter has no effect.
     * @param bool $strict whether comparison should be compared strict (`===`) or not (`==`).
     * Defaults to `false`.
     * @return bool `true` if the collection contains at least one item that matches, `false` if not.
     */
    public function contains($item, $strict = false)
    {
        if ($item instanceof Closure) {
            foreach ($this->getData() as $i) {
                if ($item($i)) {
                    return true;
                }
            }
        } else {
            foreach ($this->getData() as $i) {
                if ($strict ? $i === $item : $i == $item) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Remove a specific item from the collection.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     * @param mixed|Closure $item the item to search for. You may also pass a closure that returns a boolean.
     * The closure will be called on each item and in case it returns `true`, the item will be removed.
     * In case a closure is passed, `$strict` parameter has no effect.
     * @param bool $strict whether comparison should be compared strict (`===`) or not (`==`).
     * Defaults to `false`.
     * @return static a new collection containing the filtered items.
     * @see filter()
     */
    public function remove($item, $strict = false)
    {
        if ($item instanceof Closure) {
            $fun = function ($i) use ($item) {
                return !$item($i);
            };
        } elseif ($strict) {
            $fun = function ($i) use ($item) {
                return $i !== $item;
            };
        } else {
            $fun = function ($i) use ($item) {
                return $i != $item;
            };
        }
        return $this->filter($fun);
    }

    /**
     * Replace a specific item in the collection with another one.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     * @param mixed $item the item to search for.
     * @param mixed $replacement the replacement to insert instead of the item.
     * @param bool $strict whether comparison should be compared strict (`===`) or not (`==`).
     * Defaults to `false`.
     * @return static a new collection containing the new set of items.
     * @see map()
     */
    public function replace($item, $replacement, $strict = false)
    {
        return $this->map(function ($i) use ($item, $replacement, $strict) {
            if ($strict ? $i === $item : $i == $item) {
                return $replacement;
            }
            return $i;
        });
    }

    /**
     * Slice the set of elements by an offset and number of items to return.
     *
     * The original collection will not be changed, a new collection with the selected data is returned.
     * @param int $offset starting offset for the slice.
     * @param int|null $limit the number of elements to return at maximum.
     * @param bool $preserveKeys whether to preserve item keys.
     * @return static a new collection containing the new set of items.
     */
    public function slice($offset, $limit = null, $preserveKeys = true)
    {
        return new static(\array_slice($this->getData(), $offset, $limit, $preserveKeys));
    }

    /**
     * Apply Pagination to the collection.
     *
     * This will return a portion of the data that maps the the page calculated by the pagination object.
     *
     * Usage example:
     *
     * ```php
     * $collection = new Collection($data);
     * $pagination = new Pagination([
     *     'totalCount' => $collection->count(),
     *     'pageSize' => 3,
     * ]);
     * // the current page will be determined from request parameters
     * $pageData = $collection->paginate($pagination)->getData());
     * ```
     *
     * The original collection will not be changed, a new collection with the selected data is returned.
     * @param Pagination $pagination the pagination object to retrieve page information from.
     * @return static a new collection containing the items for the current page.
     * @see Pagination
     */
    public function paginate(Pagination $pagination, $preserveKeys = false)
    {
        $limit = $pagination->getLimit();
        return $this->slice($pagination->getOffset(), $limit > 0 ? $limit : null, $preserveKeys);
    }

    /**
     * Clones collection objects.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->getData() as &$value) {
            if (\is_object($value)) {
                $value = clone $value;
            }
        }
    }
}

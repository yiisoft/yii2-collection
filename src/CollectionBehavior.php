<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\collection;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;

/**
 * CollectionBehavior is a behavior for the ActiveQuery, which allows fetching models from database as collection instance.
 *
 * The easiest way to apply this behavior is its manual attachment to the query instance at [[\yii\db\BaseActiveRecord::find()]]
 * method. For example:
 *
 * ```php
 * class Item extend \yii\db\ActiveRecord
 * {
 *     // ...
 *     public static function find()
 *     {
 *         $query = parent::find();
 *         $query->attachBehavior('collection', \yii\collection\CollectionBehavior::class);
 *         return $query;
 *     }
 * }
 * ```
 *
 * In case you already define custom query class for your active record, you can move behavior attachment there.
 * For example:
 *
 * ```php
 * class Item extend \yii\db\ActiveRecord
 * {
 *     // ...
 *     public static function find()
 *     {
 *         return new ItemQuery(get_called_class());
 *     }
 * }
 *
 * class ItemQuery extends \yii\db\ActiveQuery
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             'collection' => [
 *                 'class' => \yii\collection\CollectionBehavior::class
 *             ],
 *         ];
 *     }
 * }
 * ```
 *
 * @see Collection
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 1.0
 */
class CollectionBehavior extends Behavior
{
    /**
     * @var string default collection class to be used at [[collect()]] method.
     */
    public $collectionClass = ModelCollection::class;
    /**
     * @var string default collection class to be used at [[batchCollect()]] method.
     */
    public $batchCollectionClass = GeneratorCollection::class;


    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        if (!$owner instanceof ActiveQueryInterface) {
            throw new InvalidConfigException('CollectionBehavior can only be attached to an ActiveQuery.');
        }
        parent::attach($owner);
    }

    /**
     * Returns query result as a collection object.
     * @param string|null $collectionClass collection class, if not set - [[collectionClass]] will be used.
     * @return ModelCollection|\yii\db\BaseActiveRecord[] models collection instance.
     */
    public function collect($collectionClass = null)
    {
        if ($collectionClass === null) {
            $collectionClass = $this->collectionClass;
        }

        return Yii::createObject(
            [
                'class' => $collectionClass,
                'query' => $this->owner,
            ],
            [
                null
            ]
        );
    }

    /**
     * Returns query result as a batch collection object.
     * @param string|null $collectionClass collection class, if not set - [[batchCollectionClass]] will be used.
     * @return GeneratorCollection|\yii\db\BaseActiveRecord[] models collection instance.
     */
    public function batchCollect($collectionClass = null)
    {
        if ($collectionClass === null) {
            $collectionClass = $this->batchCollectionClass;
        }

        return Yii::createObject(
            [
                'class' => $collectionClass,
            ],
            [
                $this->owner
            ]
        );
    }
}
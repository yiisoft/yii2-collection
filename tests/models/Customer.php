<?php

namespace yiiunit\extensions\modelcollection\models;

use yii\db\ActiveRecord;
use yii\modelcollection\CollectionBehavior;

/**
 * Customer Model
 *
 * @property int $id
 * @property string $name
 * @property int $age
 */
class Customer extends ActiveRecord
{
    public static function tableName()
    {
        return 'customers';
    }

    /**
     * @inheritdoc
     * @return \yii\db\ActiveQuery|CollectionBehavior
     */
    public static function find()
    {
        $query = parent::find();
        $query->attachBehavior('collection', CollectionBehavior::class);
        return $query;
    }

}
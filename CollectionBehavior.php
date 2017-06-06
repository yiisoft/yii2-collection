<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\modelcollection;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;

/**
 * Class CollectionBehavior
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class CollectionBehavior extends Behavior
{
    public function attach($owner)
    {
        if (!$owner instanceof ActiveQueryInterface) {
            throw new InvalidConfigException('CollectionBehavior can only be attached to an ActiveQuery.');
        }
        parent::attach($owner);
    }

    public function collect()
    {
        return new Collection(null, ['query' => $this->owner]);
    }

    public function batchCollect()
    {
        return new GeneratorCollection($this->owner);
    }

}
<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\collection;

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
    // TODO allow extension
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242117369
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242150877


    public function attach($owner)
    {
        if (!$owner instanceof ActiveQueryInterface) {
            throw new InvalidConfigException('CollectionBehavior can only be attached to an ActiveQuery.');
        }
        parent::attach($owner);
    }

    public function collect()
    {
        return new ModelCollection(null, ['query' => $this->owner]);
    }

    public function batchCollect()
    {
        return new GeneratorCollection($this->owner);
    }

}
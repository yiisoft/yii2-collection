<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\collection\models;

use yii\collection\ModelCollection;

class CustomerCollection extends ModelCollection
{
    /**
     * @return int
     */
    public function sumAge()
    {
        return $this->sum('age');
    }
}

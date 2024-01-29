<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\collection;

use yii\db\ActiveQuery;
use yii\collection\Collection;
use yiiunit\collection\models\Customer;
use yiiunit\collection\models\CustomerCollection;

class ModelCollectionTest extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication();

        \Yii::$app->db->createCommand()->createTable('customers', [
            'id' => 'pk',
            'name' => 'string NOT NULL',
            'age' => 'integer NOT NULL',
        ])->execute();

        parent::setUp();
    }

    public function testCollect()
    {
        $this->assertInstanceOf(Collection::class, Customer::find()->collect());
        $this->assertInstanceOf(ActiveQuery::class, Customer::find()->collect()->query);
        $this->assertEquals(0, Customer::find()->collect()->count());
    }

    /**
     * @depends testCollect
     */
    public function testCollectCustomClass()
    {
        $this->assertInstanceOf(CustomerCollection::class, Customer::find()->collect(CustomerCollection::class));
    }
}

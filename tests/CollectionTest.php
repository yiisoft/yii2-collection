<?php

namespace yiiunit\extensions\modelcollection;


use yii\db\ActiveQuery;
use yii\modelcollection\Collection;
use yiiunit\extensions\modelcollection\models\Customer;
use yiiunit\TestCase;

class CollectionTest extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);
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
    }
}

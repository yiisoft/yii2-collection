<?php

namespace yiiunit\extensions\collection;


use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\collection\Collection;
use yiiunit\extensions\collection\models\Customer;

class ModelCollectionTest extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => Connection::class,
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

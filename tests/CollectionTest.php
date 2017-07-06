<?php

namespace yiiunit\extensions\modelcollection;


use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\modelcollection\Collection;
use yiiunit\extensions\modelcollection\models\Customer;

class CollectionTest extends TestCase
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

    public function testIterator()
    {
        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $it = 0;
        foreach ($collection as $model) {
            $this->assertInstanceOf(Customer::class, $model);
            $this->assertEquals($it + 1, $model->id);
            ++$it;
        }
        $this->assertEquals(3, $it);
    }

    public function testArrayAccessRead()
    {
        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertInstanceOf(Customer::class, $collection[0]);
        $this->assertEquals(1, $collection[0]->id);
        $this->assertInstanceOf(Customer::class, $collection[1]);
        $this->assertEquals(2, $collection[1]->id);
        $this->assertInstanceOf(Customer::class, $collection[2]);
        $this->assertEquals(3, $collection[2]->id);

        $models = [
            'one' => new Customer(['id' => 1]),
            'two' => new Customer(['id' => 2]),
            'three' => new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertInstanceOf(Customer::class, $collection['one']);
        $this->assertEquals(1, $collection['one']->id);
        $this->assertInstanceOf(Customer::class, $collection['two']);
        $this->assertEquals(2, $collection['two']->id);
        $this->assertInstanceOf(Customer::class, $collection['three']);
        $this->assertEquals(3, $collection['three']->id);
    }

    public function testCountable()
    {
        $collection = new Collection([]);
        $this->assertEquals(0, count($collection));
        $this->assertEquals(0, $collection->count());

        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertEquals(3, count($collection));
        $this->assertEquals(3, $collection->count());
    }

    public function testMap()
    {
        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertEquals([1,2,3], $collection->map(function($model) {
            return $model->id;
        })->getModels());
    }

    public function testFilter()
    {
        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertEquals([1 => 2], $collection->filter(function($model) {
            return $model->id == 2;
        })->map(function($model) {
            return $model->id;
        })->getModels());

        $collection = new Collection($models);
        $this->assertEquals([1 => 2, 2 => 3], $collection->filter(function($model, $key) {
            return $model->id == 2 || $key == 2;
        })->map(function($model) {
            return $model->id;
        })->getModels());
    }

    public function testReduce()
    {
        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertEquals(12, $collection->reduce(function($carry, $model) {
            return $model->id + $carry;
        }, 6));
    }

    public function testSum()
    {
        $collection = new Collection([]);
        $this->assertEquals(0, $collection->sum('id'));
        $this->assertEquals(0, $collection->sum('age'));

        $models = [
            new Customer(['id' => 1, 'age' => -2]),
            new Customer(['id' => 2, 'age' => 2]),
            new Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Collection($models);
        $this->assertEquals(6, $collection->sum('id'));
        $this->assertEquals(42, $collection->sum('age'));
    }

    public function testMin()
    {
        $collection = new Collection([]);
        $this->assertEquals(0, $collection->min('id'));
        $this->assertEquals(0, $collection->min('age'));

        $models = [
            new Customer(['id' => 1, 'age' => -2]),
            new Customer(['id' => 2, 'age' => 2]),
            new Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Collection($models);
        $this->assertEquals(1, $collection->min('id'));
        $this->assertEquals(-2, $collection->min('age'));
    }

    public function testMax()
    {
        $collection = new Collection([]);
        $this->assertEquals(0, $collection->max('id'));
        $this->assertEquals(0, $collection->max('age'));

        $models = [
            new Customer(['id' => 1, 'age' => -2]),
            new Customer(['id' => 2, 'age' => 2]),
            new Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Collection($models);
        $this->assertEquals(3, $collection->max('id'));
        $this->assertEquals(42, $collection->max('age'));
    }


}

<?php

namespace yiiunit\collection;

use yii\data\Pagination;
use yii\collection\Collection;
use yiiunit\collection\models\Customer;

class CollectionTest extends TestCase
{
    protected function getIteratorModels()
    {
        return [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
    }

    public function testIterator()
    {
        $collection = new Collection($this->getIteratorModels());
        $it = 0;
        foreach ($collection as $model) {
            $this->assertInstanceOf(Customer::class, $model);
            $this->assertEquals($it + 1, $model->id);
            ++$it;
        }
        $this->assertEquals(3, $it);

        $collection = new Collection($this->getIteratorModels());
        $it = 0;
        foreach ($collection as $key => $model) {
            $this->assertInstanceOf(Customer::class, $model);
            $this->assertEquals($it, $key);
            $this->assertEquals($it + 1, $model->id);
            ++$it;
        }
        $this->assertEquals(3, $it);
    }

    public function testIteratorCurrent()
    {
        $models = $this->getIteratorModels();
        $collection = new Collection($models);
        $this->assertSame($models[0], $collection->current());
    }

    public function testIteratorKey()
    {
        $models = $this->getIteratorModels();
        $collection = new Collection($models);
        $this->assertSame(0, $collection->key());
    }

    public function testIteratorNext()
    {
        $models = $this->getIteratorModels();
        $collection = new Collection($models);
        $collection->next();
        $this->assertSame($models[1], $collection->current());
        $collection->next();
        $this->assertSame($models[2], $collection->current());
        $collection->next();
        $this->assertFalse($collection->current());
    }

    public function testIteratorValid()
    {
        $collection = new Collection([]);
        $this->assertFalse($collection->valid());

        $models = $this->getIteratorModels();
        $collection = new Collection($models);
        $this->assertTrue($collection->valid());
    }

    public function testArrayAccessRead()
    {
        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertTrue(isset($collection[0]));
        $this->assertInstanceOf(Customer::class, $collection[0]);
        $this->assertEquals(1, $collection[0]->id);
        $this->assertTrue(isset($collection[1]));
        $this->assertInstanceOf(Customer::class, $collection[1]);
        $this->assertEquals(2, $collection[1]->id);
        $this->assertTrue(isset($collection[2]));
        $this->assertInstanceOf(Customer::class, $collection[2]);
        $this->assertEquals(3, $collection[2]->id);
        $this->assertFalse(isset($collection[3]));

        $models = [
            'one' => new Customer(['id' => 1]),
            'two' => new Customer(['id' => 2]),
            'three' => new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertTrue(isset($collection['one']));
        $this->assertInstanceOf(Customer::class, $collection['one']);
        $this->assertEquals(1, $collection['one']->id);
        $this->assertTrue(isset($collection['two']));
        $this->assertInstanceOf(Customer::class, $collection['two']);
        $this->assertEquals(2, $collection['two']->id);
        $this->assertTrue(isset($collection['three']));
        $this->assertInstanceOf(Customer::class, $collection['three']);
        $this->assertEquals(3, $collection['three']->id);
        $this->assertFalse(isset($collection['four']));
    }

    /**
     * @expectedException \yii\base\InvalidCallException
     */
    public function testArrayAccessWrite()
    {
        $models = [
            'one' => new Customer(['id' => 1]),
            'two' => new Customer(['id' => 2]),
            'three' => new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $collection['three'] = 'test';
    }

    /**
     * @expectedException \yii\base\InvalidCallException
     */
    public function testArrayAccessWrite2()
    {
        $models = [
            'one' => new Customer(['id' => 1]),
            'two' => new Customer(['id' => 2]),
            'three' => new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $collection[] = 'test';
    }

    /**
     * @expectedException \yii\base\InvalidCallException
     */
    public function testArrayAccessUnset()
    {
        $models = [
            'one' => new Customer(['id' => 1]),
            'two' => new Customer(['id' => 2]),
            'three' => new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        unset($collection['two']);
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

    public function testIsEmpty()
    {
        $collection = new Collection([]);
        $this->assertTrue($collection->isEmpty());

        $models = [
            new Customer(['id' => 1]),
            new Customer(['id' => 2]),
            new Customer(['id' => 3]),
        ];
        $collection = new Collection($models);
        $this->assertFalse($collection->isEmpty());
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
        })->getData());
    }

    public function testFlatMap()
    {
        $models = [
            new Customer(['id' => 1, 'name' => [1]]),
            new Customer(['id' => 2, 'name' => [2, 3]]),
            new Customer(['id' => 3, 'name' => [4, 5]]),
        ];
        $collection = new Collection($models);
        $this->assertEquals([1,2,3,4,5], $collection->flatMap(function($model) {
            return $model->name;
        })->getData());
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
        })->getData());

        $collection = new Collection($models);
        $this->assertEquals([1 => 2, 2 => 3], $collection->filter(function($model, $key) {
            return $model->id == 2 || $key == 2;
        })->map(function($model) {
            return $model->id;
        })->getData());
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

        $collection = new Collection([-2, 1, 3]);
        $this->assertEquals(2, $collection->sum());
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

        $collection = new Collection([-2, 1, 3]);
        $this->assertEquals(-2, $collection->min());
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

        $collection = new Collection([-2, 1, 3]);
        $this->assertEquals(3, $collection->max());
    }

    public function testKeys()
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Collection($data);
        $this->assertSame([0, 'b', 1], $collection->keys()->getData());
    }

    public function testValues()
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Collection($data);
        $this->assertSame(['a', 'c', 'test'], $collection->values()->getData());
    }

    public function testFlip()
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Collection($data);
        $this->assertSame(['a' => 0, 'c' => 'b', 'test' => 1], $collection->flip()->getData());
    }

    public function testReverse()
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Collection($data);
        $this->assertSame([1 => 'test', 'b' => 'c', 0 => 'a'], $collection->reverse()->getData());
    }

    public function testMerge()
    {
        $data1 = ['a', 'b', 'c'];
        $data2 = [1, 2, 3];
        $collection1 = new Collection($data1);
        $collection2 = new Collection($data2);
        $this->assertEquals(['a', 'b', 'c', 1, 2, 3], $collection1->merge($collection2)->getData());
        $this->assertEquals([1, 2, 3, 'a', 'b', 'c'], $collection2->merge($collection1)->getData());
        $this->assertEquals(['a', 'b', 'c', 1, 2, 3], $collection1->merge($data2)->getData());
        $this->assertEquals([1, 2, 3, 'a', 'b', 'c'], $collection2->merge($data1)->getData());
    }

    /**
     * @expectedException \yii\base\InvalidParamException
     */
    public function testMergeWrongType()
    {
        $data1 = ['a', 'b', 'c'];
        $collection1 = new Collection($data1);
        $collection1->merge('string');
    }

    public function testReMap()
    {
        $models = [
            new Customer(['id' => 1, 'age' => -2]),
            new Customer(['id' => 2, 'age' => 2]),
            new Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Collection($models);
        $this->assertEquals([1 => -2, 2 => 2, 3 => 42], $collection->remap('id', 'age')->getData());
        $this->assertEquals(['1-2' => -1, '22' => 4, '342' => 45], $collection->remap(
            function($model) { return $model->id . $model->age; },
            function($model) { return $model->id + $model->age; }
        )->getData());
    }

    public function testIndexBy()
    {
        $models = [
            new Customer(['id' => 1, 'age' => -2]),
            new Customer(['id' => 2, 'age' => 2]),
            new Customer(['id' => 3, 'age' => 42]),
        ];
        $expected = [
            1 => $models[0],
            2 => $models[1],
            3 => $models[2],
        ];
        $collection = new Collection($models);
        $this->assertEquals($expected, $collection->indexBy('id')->getData());
    }

    public function testGroupBy()
    {
        $models = [
            1 => new Customer(['id' => 1, 'age' => 2]),
            2 => new Customer(['id' => 2, 'age' => 2]),
            3 => new Customer(['id' => 3, 'age' => 42]),
        ];
        $expectedByIdWithKeys = [
            1 => [
                1 => $models[1],
            ],
            2 => [
                2 => $models[2],
            ],
            3 => [
                3 => $models[3],
            ],
        ];
        $expectedByIdWithoutKeys = [
            1 => [
                $models[1],
            ],
            2 => [
                $models[2],
            ],
            3 => [
                $models[3],
            ],
        ];
        $expectedByAgeWithKeys = [
            2 => [
                1 => $models[1],
                2 => $models[2],
            ],
            42 => [
                3 => $models[3],
            ],
        ];
        $expectedByAgeWithoutKeys = [
            2 => [
                $models[1],
                $models[2],
            ],
            42 => [
                $models[3],
            ],
        ];
        $collection = new Collection($models);
        $this->assertEquals($expectedByIdWithKeys, $collection->groupBy('id')->getData());
        $this->assertEquals($expectedByIdWithoutKeys, $collection->groupBy('id', false)->getData());
        $this->assertEquals($expectedByAgeWithKeys, $collection->groupBy('age')->getData());
        $this->assertEquals($expectedByAgeWithoutKeys, $collection->groupBy('age', false)->getData());
    }

    public function testContains()
    {
        $data = [1, 2, 3, 4, 5, 6];
        $collection = new Collection($data);
        $this->assertTrue($collection->contains(1, false));
        $this->assertTrue($collection->contains('1', false));
        $this->assertTrue($collection->contains(1, true));
        $this->assertFalse($collection->contains('1', true));

        $this->assertFalse($collection->contains(8, false));
        $this->assertFalse($collection->contains('8', false));
        $this->assertFalse($collection->contains(8, true));
        $this->assertFalse($collection->contains('8', true));

        $this->assertFalse($collection->contains(function($item) { return $item > 6; }, false));
        $this->assertTrue($collection->contains(function($item) { return $item > 5; }, false));
        $this->assertFalse($collection->contains(function($item) { return $item > 6; }, true));
        $this->assertTrue($collection->contains(function($item) { return $item > 5; }, true));
    }

    public function testRemove()
    {
        $collection = new Collection([1, 2, 3, 4, 5, 6]);
        $this->assertEquals([1,2,4,5,6], $collection->remove(3, false)->values()->getData());
        $this->assertEquals([1,2,4,5,6], $collection->remove('3', false)->values()->getData());
        $this->assertEquals([1,2,4,5,6], $collection->remove(3, true)->values()->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->remove('3', true)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->remove(7, false)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->remove('7', false)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->remove(7, true)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->remove('7', true)->getData());

        $this->assertEquals([1,2,3], $collection->remove(function($i) { return $i > 3; }, false)->getData());
        $this->assertEquals([1,2,3], $collection->remove(function($i) { return $i > 3; }, true)->getData());
    }

    public function testReplace()
    {
        $collection = new Collection([1, 2, 3, 4, 5, 6]);
        $this->assertEquals([1,2,9,4,5,6], $collection->replace(3,   9, false)->getData());
        $this->assertEquals([1,2,9,4,5,6], $collection->replace('3', 9, false)->getData());
        $this->assertEquals([1,2,9,4,5,6], $collection->replace(3,   9, true)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->replace('3', 9, true)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->replace(7,   9, false)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->replace('7', 9, false)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->replace(7,   9, true)->getData());
        $this->assertEquals([1,2,3,4,5,6], $collection->replace('7', 9, true)->getData());

        $collection = new Collection([1, 2, 3, 4, 3, 6]);
        $this->assertEquals([1,2,9,4,9,6], $collection->replace(3,   9, false)->getData());
        $this->assertEquals([1,2,9,4,9,6], $collection->replace('3', 9, false)->getData());
        $this->assertEquals([1,2,9,4,9,6], $collection->replace(3,   9, true)->getData());
        $this->assertEquals([1,2,3,4,3,6], $collection->replace('3', 9, true)->getData());
        $this->assertEquals([1,2,3,4,3,6], $collection->replace(7,   9, false)->getData());
        $this->assertEquals([1,2,3,4,3,6], $collection->replace('7', 9, false)->getData());
        $this->assertEquals([1,2,3,4,3,6], $collection->replace(7,   9, true)->getData());
        $this->assertEquals([1,2,3,4,3,6], $collection->replace('7', 9, true)->getData());
    }

    public function testSort()
    {
        $data = [4, 6, 5, 8, 11, 1];
        $collection = new Collection($data);
        $this->assertEquals([1,4,5,6,8,11], $collection->sort(SORT_ASC, SORT_REGULAR)->values()->getData());
        $this->assertEquals([1,11,4,5,6,8], $collection->sort(SORT_ASC, SORT_STRING)->values()->getData());
        $this->assertEquals([11,8,6,5,4,1], $collection->sort(SORT_DESC, SORT_REGULAR)->values()->getData());
        $this->assertEquals([8,6,5,4,11,1], $collection->sort(SORT_DESC, SORT_STRING)->values()->getData());
    }

    public function testSortByKey()
    {
        $data = [5 => 4, 44 => 55, 55 => 44, 4 => 5];
        $collection = new Collection($data);
        $this->assertEquals([4 => 5, 5 => 4, 44 => 55, 55 => 44], $collection->sortByKey(SORT_ASC, SORT_REGULAR)->getData());
        $this->assertEquals([4 => 5, 44 => 55, 5 => 4, 55 => 44], $collection->sortByKey(SORT_ASC, SORT_STRING)->getData());
        $this->assertEquals([55 => 44, 44 => 55, 5 => 4, 4 => 5], $collection->sortByKey(SORT_DESC, SORT_REGULAR)->getData());
        $this->assertEquals([55 => 44, 5 => 4, 44 => 55, 4 => 5], $collection->sortByKey(SORT_DESC, SORT_STRING)->getData());
    }

    public function testSortNatural()
    {
        $data = ['100.', '1.', '11.', '2.'];
        $collection = new Collection($data);
        $this->assertEquals(['1.', '2.', '11.', '100.'], $collection->sortNatural(false)->values()->getData());
        $this->assertEquals(['1.', '2.', '11.', '100.'], $collection->sortNatural(true)->values()->getData());

        $data = ['anti', 'Auto', 'Zett', 'beta'];
        $collection = new Collection($data);
        $this->assertEquals(['anti', 'Auto', 'beta', 'Zett'], $collection->sortNatural(false)->values()->getData());
        $this->assertEquals(['Auto', 'Zett', 'anti', 'beta'], $collection->sortNatural(true)->values()->getData());
    }

    public function testSortBy()
    {
        $models = [
            2 => new Customer(['id' => 2, 'age' => 42]),
            1 => new Customer(['id' => 1, 'age' => 2]),
            3 => new Customer(['id' => 3, 'age' => 2]),
        ];
        $collection = new Collection($models);
        $this->assertSame([
            $models[1],
            $models[2],
            $models[3],
        ], $collection->sortBy('id')->getData());
        $this->assertSame([
            $models[3],
            $models[2],
            $models[1],
        ], $collection->sortBy('id', SORT_DESC)->getData());
        $this->assertSame([
            $models[1],
            $models[3],
            $models[2],
        ], $collection->sortBy(['age', 'id'])->getData());
        $this->assertSame([
            $models[3],
            $models[1],
            $models[2],
        ], $collection->sortBy(['age', 'id'], [SORT_ASC, SORT_DESC])->getData());
    }

    public function testSlice()
    {
        $data = [1,2,3,4,5];
        $collection = new Collection($data);
        $this->assertEquals([3=>4,4=>5], $collection->slice(3)->getData());
        $this->assertEquals([3=>4], $collection->slice(3, 1)->getData());
        $this->assertEquals([1,2], $collection->slice(0, 2)->getData());
        $this->assertEquals([1=>2,2=>3], $collection->slice(1, 2)->getData());
    }

    public function testPaginate()
    {
        $data = [1,2,3,4,5];
        $collection = new Collection($data);
        $pagination = new Pagination([
            'totalCount' => $collection->count(),
            'pageSize' => 3,
        ]);
        $pagination->page = 0;
        $this->assertEquals([1,2,3], $collection->paginate($pagination)->getData());
        $pagination->page = 1;
        $this->assertEquals([4,5], $collection->paginate($pagination)->getData());

        $pagination = new Pagination([
            'totalCount' => $collection->count(),
            'pageSizeLimit' => false,
            'pageSize' => -1,
        ]);
        $pagination->page = 0;
        $this->assertEquals([1,2,3,4,5], $collection->paginate($pagination)->getData());
    }
}

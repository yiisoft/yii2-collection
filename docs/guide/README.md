ActiveRecord Collection Extension for Yii 2
===========================================

TBD

Basic Usage:


Configuration
-------------

To use this extension, you have to attach the `yii\modelcollection\CollectionBehavior` to the `ActiveQuery` instance of
your `ActiveRecord` classes by overriding the `find()` method:

```php
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
```

Usage
-----

There are two options:

- one is loading all models into memory, by calling `all()` on a query: `collect()`
- The other is using PHP generators and `batch()` on a query, to allow processing of large amount of models: `batchCollect()`

### using `collect()`

You can then use the collect() method on the query to use the collection features:

#### Lazy find `with`

<https://github.com/yiisoft/yii2/issues/12743>

```php
$customerCollection = Customer::find()->collect();
$customerCollection->findWith('orders');
foreach($customerCollection as $customer) {
    // $customer->orders is now populated for every element without extra query
}
```

#### map, filter, reduce

```php
Order::find()->collect()
    ->map(function($model){ return $model->amount; })
    ->filter(function($amount){ return $amount > 100; })
    ->sum();
```

#### update, delete

<https://github.com/yiisoft/yii2/issues/13921>

Delete all customer models.

```php
// delete all models, by calling `delete()`
Customer::find()->where(['last_login' < strtotime('now - 1 year')])->collect()->deleteAll();
```

Update all:

```php
// update all models, by calling `update()`
Customer::find()->where(['last_login' < strtotime('now - 1 year')])->collect()
    ->scenario('statusUpdate')->updateAll(['status' => 'disabled']);
```

### using `batchCollect()`

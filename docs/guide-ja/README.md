Yii 2 コレクション・エクステンション
====================================

TBD

基本的な使用方法


構成
----

このエクステンションを使うためには、あなたの `ActiveRecord` クラスで `find()` メソッドをオーバーライドして、
`yii\collection\CollectionBehavior` をアタッチします。

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

使用方法
--------

二つの選択肢があります。

- 一つは、クエリで `all()` を呼び、全てのモデルをメモリに読み込む方法です : `collect()`
- もう一つは、クエリで PHP ジェネレータと `batch()` を使い、大量のモデルの処理を可能にする方法です : `batchCollect()`

### `collect()` を使う

コレクション機能を使うためにクエリで collect() メソッドを使うことが出来ます。

#### Lazy find `with`

<https://github.com/yiisoft/yii2/issues/12743>

```php
$customerCollection = Customer::find()->collect();
$customerCollection->findWith('orders');
foreach($customerCollection as $customer) {
    // $customer->orders は追加のクエリ無しに全ての要素に投入される
}
```

#### マップ、フィルタ、リデュース

```php
Order::find()->collect()
    ->map(function($model){ return $model->amount; })
    ->filter(function($amount){ return $amount > 100; })
    ->sum();
```

#### 更新、削除

<https://github.com/yiisoft/yii2/issues/13921>

全てのカスタマ・モデルを削除する。

```php
// `delete()` を呼んで全てのモデルを削除する
Customer::find()->where(['last_login' < strtotime('now - 1 year')])->collect()->deleteAll();
```

全てを更新する。

```php
// `update()` を呼んで全てのモデルを更新する
Customer::find()->where(['last_login' < strtotime('now - 1 year')])->collect()
    ->scenario('statusUpdate')->updateAll(['status' => 'disabled']);
```

### `batchCollect()` を使う

ActiveRecord Collection Extension for Yii 2
===========================================

This extension provides model collection functionality for the ActiveRecord DB layer of Yii 2.

**Development is currently in experimental state. It is not ready for production use and may change significantly.**

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-model-collection/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-model-collection)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-model-collection/downloads.png)](https://packagist.org/packages/yiisoft/yii2-model-collection)
[![Build Status](https://travis-ci.org/yiisoft/yii2-model-collection.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-model-collection)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-model-collection
```

or add

```json
"yiisoft/yii2-model-collection": "~2.0.0"
```

to the require section of your composer.json.


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

<?php

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@yiiunit/collection', __DIR__);
Yii::setAlias('@yii/collection', dirname(__DIR__) . '/src');

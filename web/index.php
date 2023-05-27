<?php

if (file_exists(__DIR__ . '/index-local.php')) include __DIR__ . '/index-local.php';

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

$profile = YII_DEBUG;
$profile and Yii::beginProfile('run');

// (new yii\web\Application($config))->run();
(new \shopack\base\common\web\Application($config))->run();

$profile and Yii::endProfile('run');

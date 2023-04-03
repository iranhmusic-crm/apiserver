<?php
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

$profile = YII_DEBUG;
$profile and Yii::beginProfile('run');

// (new yii\web\Application($config))->run();
(new \shopack\base\common\web\Application($config))->run();

$profile and Yii::endProfile('run');

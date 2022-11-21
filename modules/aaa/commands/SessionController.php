<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\modules\aaa\models\SessionModel;

class SessionController extends Controller
{
  //must be called by cron
  public function actionRemoveExpired()
  {
    //do not use `=` in condition for sleegy
    $count = SessionModel::deleteAll('ssnExpireAt < NOW()');

    echo "deleted count: {$count}";

    return ExitCode::OK;
  }
}

<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\commands;

use yii\console\Controller;
use yii\console\ExitCode;

class AlertController extends Controller
{
  //must be called by cron
  public function actionProcessQueue($maxItemCount = 100)
  {
    return ExitCode::OK;
  }
}

<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

class AlertController extends Controller
{
  //must be called by cron
  public function actionSend($maxItemCount = 100)
  {
    return ExitCode::OK;
  }
}

<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\controller;

use app\classes\controller\BaseController;
use app\classes\auth\JwtHttpBearerAuth;

class BaseRestController extends BaseController
{
	const BEHAVIOR_AUTHENTICATOR = 'authenticator';

	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR] = [
			'class' => JwtHttpBearerAuth::class,
		];

		return $behaviors;
	}

}

<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/*
namespace shopack\app\extensions\gateways\sms;

use Yii;
use shopack\base\helpers\Url;
use shopack\base\helpers\Html;
use shopack\base\helpers\ArrayHelper;
use shopack\app\classes\BaseSmsGateway;

//https://aws.asanak.ir/wiki
//Webhook
//http://.../app/gateway/webhook?key=3bf746f1-7dc6-463b-8710-05faa2740046&action=receivesms

class AsanakSmsGateway
	extends BaseSmsGateway
	implements \shopack\app\classes\ISmsGateway
		,\shopack\app\classes\IWebhook
{
	use \shopack\app\classes\WebhookTrait;
	const WEBHOOK_RECEIVE_SMS			= 'receivesms';

	//const URL_WEBSERVICE_SENDSMS = "http://www.asanak.ir/webservice/v1rest/sendsms";
	// const URL_WEBSERVICE_SENDSMS	= "http://panel.asanak.ir/webservice/v1rest/sendsms";
	const URL_WEBSERVICE_SENDSMS	= "https://panel.asanak.com/webservice/v1rest/sendsms";
	const PARAM_USERNAME					= 'username';
	const PARAM_PASSWORD					= 'password';
	const PARAM_LINENUMBER				= 'number';
	const VERB_SEND_SMS						= 'sendsms';

	public function getTitle()
	{
		return 'آسانک (Rest) ارسال سیستمی، دریافت وب هوک';
	}

	public function getParameters()
	{
		return ArrayHelper::merge(
			[
				[
					'id' => self::PARAM_USERNAME,
					'type' => 'string',
					'mandatory' => 1,
					'label' => Yii::t('appmgmt', 'User Name'),
					'style' => 'direction:ltr',
				],
				[
					'id' => self::PARAM_PASSWORD,
					'type' => 'password',
					'mandatory' => 1,
					'label' => Yii::t('appmgmt', 'Password'),
					'style' => 'direction:ltr',
				],
				[
					'id' => self::PARAM_LINENUMBER,
					'type' => 'string',
					'mandatory' => 1,
					'label' => Yii::t('appmgmt', 'Line Number'),
					'style' => 'direction:ltr',
				],
			],
			$this->validateCallerParametersDefinition()
		);
	}
	public function getVerbs()
	{
		return [
			[
				'id' => self::VERB_SEND_SMS,
				'title' => 'ارسال پیامک',
				'desc' => 'ارسال پیامک',
				'params' => [
					'username' => [
						'dir' => ['in'],
						'type' => 'string',
						'mandatory' => true,
						'label' => Yii::t('appmgmt', 'User Name'),
						'refValue' => self::PARAM_USERNAME,
					],
					'password' => [
						'dir' => ['in'],
						'type' => 'password',
						'mandatory' => true,
						'label' => Yii::t('appmgmt', 'Password'),
						'refValue' => self::PARAM_PASSWORD,
					],
					'from' => [
						'dir' => ['in'],
						'type' => 'string',
						'mandatory' => true,
						'label' => Yii::t('appmgmt', 'From'),
						'refValue' => self::PARAM_LINENUMBER,
					],
					'to' => [
						'dir' => ['in'],
						'type' => 'string',
						'mandatory' => true,
						'label' => Yii::t('appmgmt', 'To'),
					],
					'message' => [
						'dir' => ['in'],
						'type' => 'string',
						'mandatory' => true,
						'label' => Yii::t('appmgmt', 'Message'),
					],
				],
				// 'result' => [
				// ],
			],
		];
	}

// done
// Array
// (
//     [result] => Array
//         (
//             [0] => 1
//             [1] => 1
//             [2] => Array
//                 (
//                     [0] => 1215273505
//                 )

//         )

//     [request] => Array
//         (
//         )

//     [resposne] => Array
//         (
//         )

// )

	public function getLineNumber()
	{
		return $this->extensionModel->gtwPluginParameters[self::PARAM_LINENUMBER];
	}

	public function send(
		$from, //null => use default in gtwPluginParameters
		$to,
		$message
	)
	{
		if ($from == null)
			$from = $this->extensionModel->gtwPluginParameters[self::PARAM_LINENUMBER];

		try
		{
			$params = [
				'Username'		=> $this->extensionModel->gtwPluginParameters[self::PARAM_USERNAME],
				'Password'		=> $this->extensionModel->gtwPluginParameters[self::PARAM_PASSWORD],
				'Source'      => $from,
				'Destination' => $to,
				'message'     => urlencode(trim($message)),
			];
			//------------------------------------------------------
			$result = $this->post2https(self::URL_WEBSERVICE_SENDSMS, $params, [
				CURLOPT_HTTPHEADER => ['Accept: application/json'],
				CURLOPT_HEADER => 0,
				// CURLOPT_TIMEOUT => 30,
				CURLOPT_FOLLOWLOCATION => 1,
			]);
			$result = json_decode($result, true);
			//------------------------------------------------------
			$this->log(
				/* gtwlogMethodName * / 'send_sms',
				/* gtwlogRequest    * / $params,
				/* gtwlogResponse   * / $result
			);
// die(var_dump($result));
			if (isset($result['status']))
				return [false, $result];

			return [true, $result];
		}
		catch(\Exception $exp)
		{
			Yii::error($exp, __METHOD__);
			return [false, $exp->getMessage()];
		}
	}

	public function receive()
	{
		$response = [];

		return $response;
	}

	public function apiCallSendsms($verb, $verbModel, $params=[])
	{
		try
		{
			$response = $this->send(
				$verbModel->from,
				$verbModel->to,
				$verbModel->message
			);
			return [true, $response];
		}
		catch(\Exception $e)
		{
			Yii::error($e, __METHOD__);
			return [false, $e->getMessage()];
		}
	}

	//------------------------------------------------------
  // shopack\app\classes\IWebhook
	//------------------------------------------------------
	public function getWebhookCommands()
	{
		return [
			self::WEBHOOK_RECEIVE_SMS => [
				'id' => self::WEBHOOK_RECEIVE_SMS,
				'command' => self::WEBHOOK_RECEIVE_SMS,
				'title' => 'دریافت پیامک',
				'desc' => 'دریافت پیامک',
			],
		];
	}

	//?to=$TO$&body=$TEXT$&from=$FROM$
	//URL/?Destination=$Destination&Source=$Source&ReceiveTime=$ReceiveTime&MsgBody=$MsgBody
	public function callWebhook($command=null)
	{
		if ($command === null)
		{
			Yii::error('Command not provided.', __METHOD__);
			return ['result' => false, 'message' => 'Command not provided.'];
		}

		switch ($command)
		{
			case self::WEBHOOK_RECEIVE_SMS:
				$from = Html::GetOrPost(['from', 'Source']);
				if (empty($from))
					return ['result' => false, 'message' => 'from is not provided.'];
				$to = Html::GetOrPost(['to', 'Destination']);
				$body = urldecode(Html::GetOrPost(['body', 'MsgBody']));
				if (empty($body))
					return ['result' => false, 'message' => 'body is not provided.'];
				$date = null; //Html::GetOrPost(['date', 'time', 'ReceiveTime']);
				return Yii::$app->shopack->messaging->newInboundMessage($from, $to, $body, $date);
				//Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
				return ['result' => true];
			break;
		}

		Yii::error('Command not found.', __METHOD__);
		return ['result' => false, 'message' => 'Command not found.'];
	}
			//------------------------------------------------------

}

*/

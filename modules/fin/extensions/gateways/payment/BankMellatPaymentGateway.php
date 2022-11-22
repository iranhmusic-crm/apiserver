<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\fin\common\extensions\gateways\payment;

use Yii;
use SoapClient;
use yii\helpers\ArrayHelper;
use app\modules\fin\classes\BasePaymentGateway;
use app\modules\fin\classes\IPaymentGateway;

class BankMellatPaymentGateway extends BasePaymentGateway implements IPaymentGateway
{
	const URL_WEBSERVICE	= "https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl";
	const URL_GATEWAY			= "https://bpm.shaparak.ir/pgwchannel/startpay.mellat";

	const PARAM_TERMINAL_ID = 'terminalID';
	const PARAM_USERNAME = 'userName';
	const PARAM_PASSWORD = 'password';

	public function getTitle()
	{
		return 'Bank Mellat';
	}

	// public function getParameters()
	// {
	// 	return ArrayHelper::merge(parent::getParameters(), [
	// 		[
	// 			'id' => self::PARAM_TERMINAL_ID,
	// 			'type' => 'string',
	// 			'mandatory' => 1,
	// 			'label' => Yii::t('fin', 'Terminal ID'),
	// 		],
	// 		[
	// 			'id' => self::PARAM_USERNAME,
	// 			'type' => 'string',
	// 			'mandatory' => 1,
	// 			'label' => Yii::t('appmgmt', 'User Name'),
	// 		],
	// 		[
	// 			'id' => self::PARAM_PASSWORD,
	// 			'type' => 'password',
	// 			'mandatory' => 1,
	// 			'label' => Yii::t('appmgmt', 'Password'),
	// 		],
	// 	]);
	// }

	public function prepare(&$paymentModel, $callbackUrl)
	{
		$pluginParameters = $this->extensionModel->pgwPluginParameters;

		$dt = new \DateTime($paymentModel->payDateTime);
		$params = [
			'terminalId'			=> $pluginParameters[self::PARAM_TERMINAL_ID],
			'userName'				=> $pluginParameters[self::PARAM_USERNAME],
			'userPassword'		=> $pluginParameters[self::PARAM_PASSWORD],
			'orderId'					=> $paymentModel->payID,
			'amount'					=> $paymentModel->payAmount * 10, //TOMANS -> RIALS
			'localDate'				=> $dt->format("Ymd"),
			'localTime'				=> $dt->format("His"),
			'additionalData'	=> $paymentModel->payDescription,
			'callBackUrl'			=> $callbackUrl,
			'payerId'					=> 0, //TODO: (vi) ???
		];

		try
		{
			$client = new SoapClient(self::URL_WEBSERVICE, [
				'encoding' => 'UTF-8',
				'connection_timeout' => 10,
				'stream_context' => stream_context_create([
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
					],
				]),
			]);

			/******************************************************/
			$response = $client->bpPayRequest($params);
			/******************************************************/
			$this->log(
				/* gtwlogMethodName */ 'bpPayRequest',
				/* gtwlogRequest    */ $params,
				/* gtwlogResponse   */ $response
			);
			//die(print_r($response, true));
			if (isset($response->return))
			{
				$payment = explode(',', $response->return);
				if ($payment[0] != '0')
					return intval($payment[0]);

				$arr = [
					'refId' => $payment[1],
				];
				$paymentModel->payRequestParams = json_encode($arr);
				$paymentModel->save();

				return true;
			}
			else
				return [false, -1, "Invalid response"];
		}
		catch(\Exception $exp)
		{
			Yii::error($exp, __METHOD__);
			$msg = $exp->getMessage();
			$this->log(
				/* gtwlogMethodName */ 'bpPayRequest (exception)',
				/* gtwlogRequest    */ $params ?? null,
				/* gtwlogResponse   */ $msg
			);
			return [false, -1, $msg];
		}
	}

	public function run($controller, &$paymentModel, $callbackUrl)
	{
		$p = json_decode($paymentModel->payRequestParams);
		$url = self::URL_GATEWAY;

		$html =<<<HTML
<div style='text-align:center;'>
	<h2>در حال انتقال به درگاه بانک ملت</h2>
	<h3>لطفا صبور باشید.</h3>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<form id='gtwform' method='post' action='{$url}'>
		<input type='hidden' name='RefId' value='{$p->refId}' />
		<button type='submit' name='btnsubmit' class='btn btn-success'>انتقال...</button>
	</form>
</div>
<script type="text/javascript">
var timerid = setInterval(function(){
		clearInterval(timerid);
		timerid = null;
		el = document.getElementById("gtwform");
		el.submit();
	},
	500
);
</script>
HTML;

		echo $controller->renderContent($html);
		// echo $html;

		return true;
	}

	/*
	 * Check Payment Status
	 */
	public function verify(&$paymentModel)
	{
/*
MELLAT:
	GET: Array
	(
			[pid] => 115
	)
	POST: Array
	(
			[RefId] => 31DA2076B1CF2D90
			[ResCode] => 0
			[SaleOrderId] => 115
			[SaleReferenceId] => 124813059177
			[CardHolderInfo] => E3FA3B700219B267DF3BEA2ED83E4DE6D0D3CB506DFD58159EF7A600632E1AED
			[CardHolderPan] => 502229******6024
	)
*/
		$pluginParameters = $this->extensionModel->pgwPluginParameters;

		$refId = Html::GetOrPost('refId');
		if (empty($refId))
			$refId = Html::GetOrPost('RefId');
		if (empty($refId))
			return (-99001);
		$p = json_decode($paymentModel->payRequestParams);
		if ($refId != $p->refId)
			return (-99011);

		$ResCode = Html::GetOrPost('resCode');
		if ($ResCode === null)
			$ResCode = Html::GetOrPost('ResCode');
		if ($ResCode === null)
			return (-99002);
		$ResCode = intval($ResCode);
		if ($ResCode != 0)
			return $ResCode;

		$saleOrderId = Html::GetOrPost('saleOrderId');
		if ($saleOrderId === null)
			$saleOrderId = Html::GetOrPost('SaleOrderId');
		if ($saleOrderId === null)
			return (-99003);

		$saleReferenceId = Html::GetOrPost('saleReferenceId');
		if ($saleReferenceId === null)
			$saleReferenceId = Html::GetOrPost('SaleReferenceId');
		if ($saleReferenceId === null)
			return (-99004);

		//TODO: (vi) save card info
		// [CardHolderInfo] => E3FA3B700219B267DF3BEA2ED83E4DE6D0D3CB506DFD58159EF7A600632E1AED
		// [CardHolderPan] => 502229******6024

		// if ($saleOrderId != $paymentModel->payID)
			// return (-99013);

		try
		{
			$client = new SoapClient(self::URL_WEBSERVICE, [
				'encoding' => 'UTF-8',
				'connection_timeout' => 10,
				'stream_context' => stream_context_create([
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
					],
				]),
			]);

			$params = [
				'terminalId'			=> $pluginParameters[self::PARAM_TERMINAL_ID],
				'userName'				=> $pluginParameters[self::PARAM_USERNAME],
				'userPassword'		=> $pluginParameters[self::PARAM_PASSWORD],
				'orderId'					=> intval($saleOrderId), //$paymentModel->payID,
				'saleOrderId'			=> intval($saleOrderId),
				'saleReferenceId'	=> intval($saleReferenceId),
			];
			/******************************************************/
			$response = $client->bpVerifyRequest($params);
			/******************************************************/
			$this->log(
				/* gtwlogMethodName */ 'bpVerifyRequest',
				/* gtwlogRequest    */ $params,
				/* gtwlogResponse   */ $response
			);
			if (!isset($response->return))
				return [false, -1, "Invalid response"];

			$payment = explode(',', $response->return);
			if ($payment[0] != '0')
				return intval($payment[0]);

			$paymentModel->payTransactionRefID = "{$refId}";
			$paymentModel->save();

			//settle
			try
			{
				$params = [
					'terminalId'			=> $pluginParameters[self::PARAM_TERMINAL_ID],
					'userName'				=> $pluginParameters[self::PARAM_USERNAME],
					'userPassword'		=> $pluginParameters[self::PARAM_PASSWORD],
					'orderId'					=> intval($saleOrderId), //$paymentModel->payID,
					'saleOrderId'			=> intval($saleOrderId),
					'saleReferenceId'	=> intval($saleReferenceId),
				];
				/******************************************************/
				$response = $client->bpSettleRequest($params);
				/******************************************************/
				$this->log(
					/* gtwlogMethodName */ 'bpSettleRequest',
					/* gtwlogRequest    */ $params,
					/* gtwlogResponse   */ $response
				);
				// Yii::trace(print_r($response, true), __METHOD__);

				if (!isset($response->return))
					return [false, -1, "Invalid settle response"];

				$rets = explode(',', $response->return);
				if ($rets[0] != '0')
					return intval($rets[0]);

				if (empty($paymentModel->payGatewayResponseMessage))
					$paymentModel->payGatewayResponseMessage = 'Request Settled';
				else
					$paymentModel->payGatewayResponseMessage = $paymentModel->payGatewayResponseMessage
						. '. '
						. 'Request Settled';
				$paymentModel->save();
			}
			catch(\Exception $exp)
			{
				Yii::error($exp, __METHOD__);
				$msg = $exp->getMessage();
				$this->log(
					/* gtwlogMethodName */ 'bpSettleRequest (exception)',
					/* gtwlogRequest    */ $params ?? null,
					/* gtwlogResponse   */ $msg
				);
				//settle is optional??
				return [false, -1, $msg];
			}

			//done
			return true;
		}
		catch(\Exception $exp)
		{
			Yii::error($exp, __METHOD__);
			$msg = $exp->getMessage();
			$this->log(
				/* gtwlogMethodName */ 'verify (exception)',
				/* gtwlogRequest    */ $params ?? null,
				/* gtwlogResponse   */ $msg
			);
			return [false, -1, $msg];
		}
	}

	public function getErrorMessage($error)
	{
		switch ($error)
		{
			case (0)  : return 'تراکنش با موفقیت انجام شد.';
			case (11) : return 'شماره کارت نامعتبر است.';
			case (12) : return 'موجودی کافی نیست.';
			case (13) : return 'رمز نادرست است.';
			case (14) : return 'تعداد دفعات وارد کردن رمز بیش از حد مجاز است.';
			case (15) : return 'کارت نامعتبر است.';
			case (16) : return 'دفعات برداشت وجه بیش از حد مجاز است.';
			case (17) : return 'کاربر از انجام تراکنش منصرف شده است.';
			case (18) : return 'تاریخ انقضای کارت گذشته است.';
			case (19) : return 'مبلغ برداشت وجه بیش از حد مجاز است.';
			case (111): return 'صادر کننده کارت نامعتبر است.';
			case (112): return 'خطای سوییچ صادر کننده کارت';
			case (113): return 'پاسخی از صادر کننده کارت دریافت نشد.';
			case (114): return 'دارنده کارت مجاز به انجام این تراکنش نیست.';
			case (21) : return 'پذیرنده نامعتبر است.';
			case (23) : return 'خطای امنیتی رخ داده است.';
			case (24) : return 'اطلاعات کاربری 	پذیرنده نامعتبر است.';
			case (25) : return 'مبلغ نامعتبر است.';
			case (31) : return 'پاسخ نامعتبر است.';
			case (32) : return 'فرمت اطلاعات وارد شده صحیح نمی‌باشد.';
			case (33) : return 'حساب نامعتبر است.';
			case (34) : return 'خطای سیستمی';
			case (35) : return 'تاریخ نامعتبر است.';
			case (41) : return 'شماره درخواست تکراری است.';
			case (42) : return 'تراکنش Sale یافت نشد.';
			case (43) : return 'قبلا درخواست Verify داده شده است.';
			case (44) : return 'درخواست Verify یافت نشد.';
			case (45) : return 'تراکنش Settle شده است.';
			case (46) : return 'تراکنش Settle نشده است.';
			case (47) : return 'تراکنش Settle یافت نشد.';
			case (48) : return 'تراکنش Reverse نشده است.';
			case (49) : return 'تراکنش Refund یافت نشد.';
			case (412): return 'شناسه قبض نادرست است.';
			case (413): return 'شناسه پرداخت نادرست است.';
			case (414): return 'سازمان صادر کننده قبض نامعتبر است.';
			case (415): return 'زمان جلسه کاری به پایان رسیده است.';
			case (416): return 'خطا در ثبت اطلاعات';
			case (417): return 'شناسه پرداخت کننده نامعتبر است.';
			case (418): return 'اشکال در تعریف اطلاعات مشتری';
			case (419): return 'تعداد دفعات ورود اطلاعات از حد مجاز گذشته است.';
			case (421): return 'IP نامعتبر است.';
			case (51) : return 'تراکنش تکراری است.';
			case (54) : return 'تراکنش مرجع موجود نیست.';
			case (55) : return 'تراکنش نامعتبر است.';
			case (61) : return 'خطا در واریز';
		}

		return "خطای ناشناخته {$error}";
	}

}

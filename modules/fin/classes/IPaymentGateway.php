<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\fin\classes;

interface IPaymentGateway
{
	public function prepare(&$paymentModel, $callbackUrl);
	public function run($controller, &$paymentModel, $callbackUrl);
	public function verify(&$paymentModel);
}

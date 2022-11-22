<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\helpers;

use libphonenumber\PhoneNumberUtil;

class PhoneHelper
{
	static function normalizePhoneNumber($mobile, $country = 'IR')
	{
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneNumber = $phoneUtil->parse($mobile, $country);

		if ($phoneUtil->isValidNumber($phoneNumber))
			return $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);

		return false;
	}

}

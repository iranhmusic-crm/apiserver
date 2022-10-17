<?php

use yii\db\Migration;
use yii\db\Expression;

class m221015_160300_init extends Migration
{
	public function up()
	{
		$this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `{{%user}}` (
  `usrID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usrFirstName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usrLastName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usrGender` tinyint(3) unsigned DEFAULT NULL,
  `usrEmail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usrEmailApprovedAt` timestamp NULL DEFAULT NULL,
  `usrConfirmEmailToken` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrBirthDate` date DEFAULT NULL,
  `usrSSID` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrMobile` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrMobileConfirmToken` mediumint(9) DEFAULT NULL,
  `usrMobileApprovedAt` timestamp NULL DEFAULT NULL,
  `usrGdvID` bigint(20) unsigned DEFAULT NULL,
  `usrGdvID_OtherDistrict` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrGdvID_OtherMahale` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrHomeAddress` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrZipCode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrMilitaryStatus` tinyint(3) unsigned DEFAULT NULL,
  `usrMaritalStatus` tinyint(3) unsigned DEFAULT NULL,
  `usrAuthKey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usrPasswordHash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usrPasswordResetToken` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrStatus` smallint(6) NOT NULL DEFAULT '10',
  `usrImage` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrAddressCoordinates` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrSignupCoordinates` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrLastLoginCoordinates` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrCreatedAt` timestamp NOT NULL,
  `usrCreatedBy` bigint(20) unsigned DEFAULT NULL,
  `usrUpdatedAt` timestamp NULL DEFAULT NULL,
  `usrUpdatedBy` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`usrID`),
  UNIQUE KEY `email` (`usrEmail`),
  UNIQUE KEY `password_reset_token` (`usrPasswordResetToken`),
  UNIQUE KEY `usrConfirmEmailToken` (`usrConfirmEmailToken`),
  UNIQUE KEY `usrMobile` (`usrMobile`),
  KEY `FK_{{%user}}_tbl_app_geo_division` (`usrGdvID`),
  KEY `FK_{{%user}}_{{%user}}_creator` (`usrCreatedBy`),
  KEY `FK_{{%user}}_{{%user}}_modifier` (`usrUpdatedBy`),
  CONSTRAINT `FK_{{%user}}_tbl_app_geo_division` FOREIGN KEY (`usrGdvID`) REFERENCES `tbl_app_geo_division` (`gdvid`),
  CONSTRAINT `FK_{{%user}}_{{%user}}_creator` FOREIGN KEY (`usrCreatedBy`) REFERENCES `{{%user}}` (`usrid`),
  CONSTRAINT `FK_{{%user}}_{{%user}}_modifier` FOREIGN KEY (`usrUpdatedBy`) REFERENCES `{{%user}}` (`usrid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
		);

		$this->batchInsertIgnore('{{%user}}', ['usrID', 'usrFirstName', 'usrLastName', 'usrGender', 'usrEmail', 'usrEmailApprovedAt', 'usrConfirmEmailToken', 'usrBirthDate', 'usrSSID', 'usrMobile', 'usrMobileConfirmToken', 'usrMobileApprovedAt', 'usrGdvID', 'usrGdvID_OtherDistrict', 'usrGdvID_OtherMahale', 'usrHomeAddress', 'usrZipCode', 'usrMilitaryStatus', 'usrMaritalStatus', 'usrAuthKey', 'usrPasswordHash', 'usrPasswordResetToken', 'usrStatus', 'usrImage', 'usrAddressCoordinates', 'usrSignupCoordinates', 'usrLastLoginCoordinates', 'usrCreatedAt', 'usrCreatedBy', 'usrUpdatedAt', 'usrUpdatedBy'], [
			[1, 'مدیر کل', 'سیستم', NULL, 'superadmin@site.dom', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'zbV3Y5EEbdB7f6GUDLYpKq2GIeLcFaFB', '$2y$13$wqJKRr1OBE5ftCzsN9GNH.HlvrDC7UDGmw53SX4zrtDNtgbeOrejG', NULL, 1, NULL, NULL, NULL, NULL, new Expression('NOW()'), NULL, NULL, NULL],
			[5, 'کامبیز', 'زندی', 1, 'kambizzandi@gmail.com', NULL, NULL, NULL, NULL, '+989122983610', NULL, NULL, 91964, NULL, NULL, NULL, NULL, NULL, NULL, 'c7c_UcLyfFRd6-gP8KkuNpHVn6yhxYIt', '$2y$13$6n.PN8QpVWXdlP7sDqO.aOlX1V/ZsObdzZw.MWcXCXSJuI7qYj5Di', NULL, 10, NULL, NULL, '35.699763,51.338085', '', new Expression('NOW()'), 1, NULL, NULL],
		]);

		$this->execute(<<<SQLSTR
ALTER TABLE `{{%user}}` AUTO_INCREMENT=101;
SQLSTR
		);
	}

	public function down()
	{
		$this->dropTable('{{%user}}');
	}
}

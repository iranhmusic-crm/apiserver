<?php

use yii\db\Migration;
use yii\db\Expression;

class m221015_160300_init extends Migration
{
	public function up()
	{
		$this->execute(<<<SQLSTR
CREATE TABLE `tblGeoCountry` (
  `cntrID` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cntrName` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`cntrID`)
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;
SQLSTR
		);

		$this->execute(<<<SQLSTR
CREATE TABLE `tblGeoState` (
  `sttID` MEDIUMINT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sttName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `sttCountryID` SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`sttID`) USING BTREE,
  INDEX `FK_tblGeoState_tblGeoCountry` (`sttCountryID`) USING BTREE,
  CONSTRAINT `FK_tblGeoState_tblGeoCountry` FOREIGN KEY (`sttCountryID`) REFERENCES `tblGeoCountry` (`cntrID`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE `tblGeoCityOrVillage` (
  `ctvID` MEDIUMINT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ctvName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `ctvStateID` MEDIUMINT(7) UNSIGNED NOT NULL,
  `ctvType` CHAR(1) NOT NULL DEFAULT 'C' COMMENT 'C:City, V:Village' COLLATE 'utf8mb4_unicode_ci',
  PRIMARY KEY (`ctvID`) USING BTREE,
  INDEX `FK_tblGeoCityOrVillage_tblGeoState` (`ctvStateID`) USING BTREE,
  CONSTRAINT `FK_tblGeoCityOrVillage_tblGeoState` FOREIGN KEY (`ctvStateID`) REFERENCES `tblGeoState` (`sttID`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE `tblGeoTown` (
	`twnID` MEDIUMINT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
	`twnName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`twnCityID` MEDIUMINT(7) UNSIGNED NOT NULL,
	PRIMARY KEY (`twnID`) USING BTREE,
	INDEX `FK_tblGeoTown_tblGeoCityOrVillage` (`twnCityID`) USING BTREE,
	CONSTRAINT `FK_tblGeoTown_tblGeoCityOrVillage` FOREIGN KEY (`twnCityID`) REFERENCES `tblGeoCityOrVillage` (`ctvID`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;
SQLSTR
    );

		$this->execute(<<<SQLSTR
CREATE TABLE `tblUser` (
  `usrID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usrGender` CHAR(1) NULL DEFAULT NULL COMMENT 'M:Male, F:Female' COLLATE 'utf8mb4_unicode_ci',
  `usrFirstName` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrLastName` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrEmail` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrEmailApprovedAt` TIMESTAMP NULL DEFAULT NULL,
  `usrConfirmEmailToken` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMobile` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMobileConfirmToken` MEDIUMINT(7) NULL DEFAULT NULL,
  `usrMobileApprovedAt` TIMESTAMP NULL DEFAULT NULL,
  `usrSSID` VARCHAR(16) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrAuthKey` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrPasswordHash` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrPasswordResetToken` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrCountryID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `usrStateID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `usrCityOrVillageID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `usrTownID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `usrBirthDate` DATE NULL DEFAULT NULL,
  `usrHomeAddress` VARCHAR(2048) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrZipCode` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMilitaryStatus` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `usrMaritalStatus` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `usrImage` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrAddressCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrSignupCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrLastLoginCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, D:Disable, R:Removed' COLLATE 'utf8mb4_unicode_ci',
  `usrCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usrCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `usrUpdatedAt` DATETIME NULL DEFAULT NULL,
  `usrUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`usrID`) USING BTREE,
  UNIQUE INDEX `usrEmail` (`usrEmail`) USING BTREE,
  UNIQUE INDEX `usrMobile` (`usrMobile`) USING BTREE,
  UNIQUE INDEX `usrSSID` (`usrSSID`) USING BTREE,
  UNIQUE INDEX `password_reset_token` (`usrPasswordResetToken`) USING BTREE,
  UNIQUE INDEX `usrConfirmEmailToken` (`usrConfirmEmailToken`) USING BTREE,
  INDEX `FK_tblUser_tblUser_creator` (`usrCreatedBy`) USING BTREE,
  INDEX `FK_tblUser_tblUser_modifier` (`usrUpdatedBy`) USING BTREE,
  INDEX `FK_tblUser_tblGeoCountry` (`usrCountryID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoState` (`usrStateID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoCityOrVillage` (`usrCityOrVillageID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoTown` (`usrTownID`) USING BTREE,
  CONSTRAINT `FK_tblUser_tblGeoCityOrVillage` FOREIGN KEY (`usrCityOrVillageID`) REFERENCES `tblGeoCityOrVillage` (`ctvID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblGeoCountry` FOREIGN KEY (`usrCountryID`) REFERENCES `tblGeoCountry` (`cntrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblGeoState` FOREIGN KEY (`usrStateID`) REFERENCES `tblGeoState` (`sttID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblGeoTown` FOREIGN KEY (`usrTownID`) REFERENCES `tblGeoTown` (`twnID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblUser_creator` FOREIGN KEY (`usrCreatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblUser_modifier` FOREIGN KEY (`usrUpdatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;

SQLSTR
		);
//)w,Ps&_2BpKe
//N9qlkkKr)S25

    $this->batchInsertIgnore('{{%User}}', ['usrID', 'usrFirstName', 'usrLastName', 'usrGender', 'usrEmail', 'usrMobile', 'usrAuthKey', 'usrPasswordHash', 'usrStatus'], [
			[1, 'مدیر کل', 'سیستم', NULL, 'superadmin@site.dom', NULL, 'zbV3Y5EEbdB7f6GUDLYpKq2GIeLcFaFB', '$2y$13$wqJKRr1OBE5ftCzsN9GNH.HlvrDC7UDGmw53SX4zrtDNtgbeOrejG', 'D'],
			[52, 'کامبیز', 'زندی', 'M', 'kambizzandi@gmail.com', '+989122983610', 'c7c_UcLyfFRd6-gP8KkuNpHVn6yhxYIt', '$2y$13$6n.PN8QpVWXdlP7sDqO.aOlX1V/ZsObdzZw.MWcXCXSJuI7qYj5Di', 'A'],
		]);

		$this->execute(<<<SQLSTR
ALTER TABLE `{{%User}}` AUTO_INCREMENT=101;
SQLSTR
		);
	}

	public function down()
	{
		$this->dropTable('{{%User}}');
	}
}

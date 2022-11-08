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
  `usrEmail` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrEmailApprovedAt` TIMESTAMP NULL DEFAULT NULL,
  `usrConfirmEmailToken` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMobile` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMobileConfirmToken` MEDIUMINT(7) NULL DEFAULT NULL,
  `usrMobileApprovedAt` TIMESTAMP NULL DEFAULT NULL,
  `usrSSID` VARCHAR(16) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrAuthKey` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrPasswordHash` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrPasswordResetToken` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
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
  CONSTRAINT `FK_tblUser_tblUser_creator` FOREIGN KEY (`usrCreatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblUser_modifier` FOREIGN KEY (`usrUpdatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;
SQLSTR
		);

		$this->execute(<<<SQLSTR
CREATE TABLE `tblProfile` (
  `prf_usrID` BIGINT(20) UNSIGNED NOT NULL,
  `prfGender` CHAR(1) NULL DEFAULT NULL COMMENT 'M:Male, F:Female' COLLATE 'utf8mb4_unicode_ci',
  `prfFirstName` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfLastName` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfCountryID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `prfStateID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `prfCityOrVillageID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `prfTownID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `prfBirthDate` DATE NULL DEFAULT NULL,
  `prfHomeAddress` VARCHAR(2048) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfZipCode` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfMilitaryStatus` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `prfMaritalStatus` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `prfImage` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfAddressCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfSignupCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfLastLoginCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `prfCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prfCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `prfUpdatedAt` DATETIME NULL DEFAULT NULL,
  `prfUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`prf_usrID`) USING BTREE,
  INDEX `FK_tblProfile_tblUser_creator` (`prfCreatedBy`) USING BTREE,
  INDEX `FK_tblProfile_tblUser_modifier` (`prfUpdatedBy`) USING BTREE,
  INDEX `FK_tblProfile_tblGeoCountry` (`prfCountryID`) USING BTREE,
  INDEX `FK_tblProfile_tblGeoState` (`prfStateID`) USING BTREE,
  INDEX `FK_tblProfile_tblGeoCityOrVillage` (`prfCityOrVillageID`) USING BTREE,
  INDEX `FK_tblProfile_tblGeoTown` (`prfTownID`) USING BTREE,
  CONSTRAINT `FK_tblProfile_tblGeoCityOrVillage` FOREIGN KEY (`prfCityOrVillageID`) REFERENCES `tblGeoCityOrVillage` (`ctvID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblProfile_tblGeoCountry` FOREIGN KEY (`prfCountryID`) REFERENCES `tblGeoCountry` (`cntrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblProfile_tblGeoState` FOREIGN KEY (`prfStateID`) REFERENCES `tblGeoState` (`sttID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblProfile_tblGeoTown` FOREIGN KEY (`prfTownID`) REFERENCES `tblGeoTown` (`twnID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblProfile_tblUser` FOREIGN KEY (`prf_usrID`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE CASCADE,
  CONSTRAINT `FK_tblProfile_tblUser_creator` FOREIGN KEY (`prfCreatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblProfile_tblUser_modifier` FOREIGN KEY (`prfUpdatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
DEFAULT CHARSET=utf8mb4
ENGINE=InnoDB
;
SQLSTR
    );



    //)w,Ps&_2BpKe
//N9qlkkKr)S25

    $this->batchInsertIgnore('{{%User}}', ['usrID', 'usrEmail', 'usrMobile', 'usrAuthKey', 'usrPasswordHash', 'usrStatus'], [
			[1, 'system@site.dom', NULL, 'zbV3Y5EEbdB7f6GUDLYpKq2GIeLcFaFB', NULL, 'D'],
			[52, 'kambizzandi@gmail.com', '+989122983610', 'c7c_UcLyfFRd6-gP8KkuNpHVn6yhxYIt', '$2y$13$6n.PN8QpVWXdlP7sDqO.aOlX1V/ZsObdzZw.MWcXCXSJuI7qYj5Di', 'A'],
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

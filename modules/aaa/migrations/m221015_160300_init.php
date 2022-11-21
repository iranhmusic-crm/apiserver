<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\db\Migration;
use yii\db\Expression;

class m221015_160300_init extends Migration
{
	public function up()
	{
		$this->execute(<<<SQLSTR
CREATE TABLE `tblRole` (
  `rolID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rolName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
  `rolParentID` INT(10) UNSIGNED NULL DEFAULT NULL,
  `rolPrivs` JSON NOT NULL,
  `rolCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rolCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `rolUpdatedAt` DATETIME NULL DEFAULT NULL,
  `rolUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`rolID`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
		);

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
  `usrRoleID` INT(10) UNSIGNED NULL DEFAULT NULL,
  `usrPrivs` JSON NULL DEFAULT NULL,
  `usrEmail` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrEmailApprovedAt` TIMESTAMP NULL DEFAULT NULL,
  `usrConfirmEmailToken` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMobile` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrMobileConfirmToken` MEDIUMINT(7) NULL DEFAULT NULL,
  `usrMobileApprovedAt` TIMESTAMP NULL DEFAULT NULL,
  `usrSSID` VARCHAR(16) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrPasswordHash` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrPasswordResetToken` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrGender` CHAR(1) NULL DEFAULT NULL COMMENT 'M:Male, F:Female' COLLATE 'utf8mb4_unicode_ci',
  `usrFirstName` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrLastName` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrCountryID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `usrStateID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `usrCityOrVillageID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `usrTownID` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
  `usrBirthDate` DATE NULL DEFAULT NULL,
  `usrHomeAddress` VARCHAR(2048) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrZipCode` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrImage` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrSignupCoordinates` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `usrStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, D:Disable, R:Removed' COLLATE 'utf8mb4_unicode_ci',
  `usrCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usrCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `usrUpdatedAt` DATETIME NULL DEFAULT NULL,
  `usrUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `usrRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `usrRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`usrID`) USING BTREE,
  UNIQUE INDEX `password_reset_token` (`usrPasswordResetToken`) USING BTREE,
  UNIQUE INDEX `usrConfirmEmailToken` (`usrConfirmEmailToken`) USING BTREE,
  UNIQUE INDEX `usrEmail_usrRemovedAt` (`usrEmail`, `usrRemovedAt`) USING BTREE,
  UNIQUE INDEX `usrMobile_usrRemovedAt` (`usrMobile`, `usrRemovedAt`) USING BTREE,
  UNIQUE INDEX `usrSSID_usrRemovedAt` (`usrSSID`, `usrRemovedAt`) USING BTREE,
  INDEX `FK_tblUser_tblUser_creator` (`usrCreatedBy`) USING BTREE,
  INDEX `FK_tblUser_tblUser_modifier` (`usrUpdatedBy`) USING BTREE,
  INDEX `FK_tblUser_tblRole` (`usrRoleID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoCountry` (`usrCountryID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoState` (`usrStateID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoCityOrVillage` (`usrCityOrVillageID`) USING BTREE,
  INDEX `FK_tblUser_tblGeoTown` (`usrTownID`) USING BTREE,
  CONSTRAINT `FK_tblUser_tblGeoCityOrVillage` FOREIGN KEY (`usrCityOrVillageID`) REFERENCES `tblGeoCityOrVillage` (`ctvID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblGeoCountry` FOREIGN KEY (`usrCountryID`) REFERENCES `tblGeoCountry` (`cntrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblGeoState` FOREIGN KEY (`usrStateID`) REFERENCES `tblGeoState` (`sttID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblGeoTown` FOREIGN KEY (`usrTownID`) REFERENCES `tblGeoTown` (`twnID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblRole` FOREIGN KEY (`usrRoleID`) REFERENCES `tblRole` (`rolID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblUser_creator` FOREIGN KEY (`usrCreatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT `FK_tblUser_tblUser_modifier` FOREIGN KEY (`usrUpdatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
		);

		$this->execute(<<<SQLSTR
CREATE TABLE `tblSession` (
	`ssnID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ssnUserID` BIGINT(20) UNSIGNED NOT NULL,
	`ssnJWT` VARCHAR(2048) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ssnMd5JWT` VARCHAR(32) AS (md5(`ssnJWT`)) virtual,
	`ssnStatus` CHAR(1) NOT NULL DEFAULT 'P' COMMENT 'P:Pending, A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`ssnExpireAt` DATETIME NULL DEFAULT NULL,
	`ssnCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ssnUpdatedAt` DATETIME NULL DEFAULT NULL,
	`ssnUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`ssnID`) USING BTREE,
	UNIQUE INDEX `ssnMd5JWT` (`ssnMd5JWT`) USING BTREE,
	INDEX `FK_tblSession_tblUser` (`ssnUserID`) USING BTREE,
	INDEX `FK_tblSession_tblUser_modifier` (`ssnUpdatedBy`) USING BTREE,
	CONSTRAINT `FK_tblSession_tblUser` FOREIGN KEY (`ssnUserID`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `FK_tblSession_tblUser_modifier` FOREIGN KEY (`ssnUpdatedBy`) REFERENCES `tblUser` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
  );










    //)w,Ps&_2BpKe
//N9qlkkKr)S25

    $this->batchInsertIgnore('{{%Role}}', ['rolID', 'rolName', 'rolParentID', 'rolPrivs'], [
      [ 1, 'Full Access', NULL, '{"*":1}'],
      [10, 'User',        NULL, '{"user":{"login":1,"logout":1}}'],
    ]);

    $this->execute(<<<SQLSTR
ALTER TABLE `{{%Role}}` AUTO_INCREMENT=101;
SQLSTR
		);

    $this->batchInsertIgnore('{{%User}}', [
      'usrID',
      'usrRoleID',
      'usrEmail',
      'usrMobile',
      'usrGender',
      'usrFirstName',
      'usrLastName',
      'usrStatus',
    ], [
      [ 1, NULL, 'system@site.dom',       NULL,            NULL, NULL,     NULL,    'D'],
			[52, 1,    'kambizzandi@gmail.com', '+989122983610', 'M',  'Kambiz', 'Zandi', 'A'],
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

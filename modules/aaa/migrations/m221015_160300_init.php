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
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoCountry` (
  `cntrID` smallint unsigned NOT NULL AUTO_INCREMENT,
  `cntrName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`cntrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoState` (
  `sttID` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `sttName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sttCountryID` smallint unsigned NOT NULL,
  PRIMARY KEY (`sttID`) USING BTREE,
  KEY `FK_tbl_AAA_GeoState_tbl_AAA_GeoCountry` (`sttCountryID`) USING BTREE,
  CONSTRAINT `FK_tbl_AAA_GeoState_tbl_AAA_GeoCountry` FOREIGN KEY (`sttCountryID`) REFERENCES `tbl_AAA_GeoCountry` (`cntrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoCityOrVillage` (
  `ctvID` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `ctvName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ctvStateID` mediumint unsigned NOT NULL,
  `ctvType` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'C' COMMENT 'C:City, V:Village',
  PRIMARY KEY (`ctvID`) USING BTREE,
  KEY `FK_tbl_AAA_GeoCityOrVillage_tbl_AAA_GeoState` (`ctvStateID`) USING BTREE,
  CONSTRAINT `FK_tbl_AAA_GeoCityOrVillage_tbl_AAA_GeoState` FOREIGN KEY (`ctvStateID`) REFERENCES `tbl_AAA_GeoState` (`sttID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoTown` (
  `twnID` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `twnName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `twnCityID` mediumint unsigned NOT NULL,
  PRIMARY KEY (`twnID`) USING BTREE,
  KEY `FK_tbl_AAA_GeoTown_tbl_AAA_GeoCityOrVillage` (`twnCityID`) USING BTREE,
  CONSTRAINT `FK_tbl_AAA_GeoTown_tbl_AAA_GeoCityOrVillage` FOREIGN KEY (`twnCityID`) REFERENCES `tbl_AAA_GeoCityOrVillage` (`ctvID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_Role` (
  `rolID` int unsigned NOT NULL AUTO_INCREMENT,
  `rolName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolParentID` int unsigned DEFAULT NULL,
  `rolPrivs` json NOT NULL,
  `rolCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rolCreatedBy` bigint unsigned DEFAULT NULL,
  `rolUpdatedAt` datetime DEFAULT NULL,
  `rolUpdatedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`rolID`),
  KEY `FK_tbl_AAA_Role_tbl_AAA_User_creator` (`rolCreatedBy`),
  KEY `FK_tbl_AAA_Role_tbl_AAA_User_modifier` (`rolUpdatedBy`),
  CONSTRAINT `FK_tbl_AAA_Role_tbl_AAA_User_creator` FOREIGN KEY (`rolCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_Role_tbl_AAA_User_modifier` FOREIGN KEY (`rolUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_User` (
  `usrID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usrRoleID` int unsigned DEFAULT NULL,
  `usrPrivs` json DEFAULT NULL,
  `usrEmail` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrEmailApprovedAt` datetime DEFAULT NULL,
  `usrMobile` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrMobileApprovedAt` datetime DEFAULT NULL,
  `usrSSID` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrPasswordHash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrPasswordCreatedAt` datetime DEFAULT NULL,
  `usrGender` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M:Male, F:Female',
  `usrFirstName` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrLastName` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrCountryID` smallint unsigned DEFAULT NULL,
  `usrStateID` mediumint unsigned DEFAULT NULL,
  `usrCityOrVillageID` mediumint unsigned DEFAULT NULL,
  `usrTownID` mediumint unsigned DEFAULT NULL,
  `usrBirthDate` date DEFAULT NULL,
  `usrHomeAddress` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrZipCode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrImage` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrSignupCoordinates` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'A:Active, D:Disable, R:Removed',
  `usrCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usrCreatedBy` bigint unsigned DEFAULT NULL,
  `usrUpdatedAt` datetime DEFAULT NULL,
  `usrUpdatedBy` bigint unsigned DEFAULT NULL,
  `usrRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `usrRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`usrID`) USING BTREE,
  UNIQUE KEY `usrEmail_usrRemovedAt` (`usrEmail`,`usrRemovedAt`),
  UNIQUE KEY `usrMobile_usrRemovedAt` (`usrMobile`,`usrRemovedAt`),
  UNIQUE KEY `usrSSID_usrRemovedAt` (`usrSSID`,`usrRemovedAt`),
  KEY `FK_tbl_AAA_User_tbl_AAA_User_creator` (`usrCreatedBy`) USING BTREE,
  KEY `FK_tbl_AAA_User_tbl_AAA_User_modifier` (`usrUpdatedBy`) USING BTREE,
  KEY `FK_tbl_AAA_User_tbl_AAA_Role` (`usrRoleID`),
  KEY `FK_tbl_AAA_User_tbl_AAA_GeoCountry` (`usrCountryID`),
  KEY `FK_tbl_AAA_User_tbl_AAA_GeoState` (`usrStateID`),
  KEY `FK_tbl_AAA_User_tbl_AAA_GeoCityOrVillage` (`usrCityOrVillageID`),
  KEY `FK_tbl_AAA_User_tbl_AAA_GeoTown` (`usrTownID`),
  KEY `FK_tbl_AAA_User_tbl_AAA_User_remover` (`usrRemovedBy`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_User_remover` FOREIGN KEY (`usrRemovedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_GeoCityOrVillage` FOREIGN KEY (`usrCityOrVillageID`) REFERENCES `tbl_AAA_GeoCityOrVillage` (`ctvID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_GeoCountry` FOREIGN KEY (`usrCountryID`) REFERENCES `tbl_AAA_GeoCountry` (`cntrID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_GeoState` FOREIGN KEY (`usrStateID`) REFERENCES `tbl_AAA_GeoState` (`sttID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_GeoTown` FOREIGN KEY (`usrTownID`) REFERENCES `tbl_AAA_GeoTown` (`twnID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_Role` FOREIGN KEY (`usrRoleID`) REFERENCES `tbl_AAA_Role` (`rolID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_User_creator` FOREIGN KEY (`usrCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_User_modifier` FOREIGN KEY (`usrUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_Session` (
  `ssnID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ssnUserID` bigint unsigned NOT NULL,
  `ssnJWT` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssnJWTMD5` varchar(32) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (md5(`ssnJWT`)) VIRTUAL,
  `ssnStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P' COMMENT 'P:Pending, A:Active, R:Removed',
  `ssnExpireAt` datetime DEFAULT NULL,
  `ssnCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ssnUpdatedAt` datetime DEFAULT NULL,
  `ssnUpdatedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`ssnID`),
  UNIQUE KEY `ssnMd5JWT` (`ssnJWTMD5`) USING BTREE,
  KEY `FK_tbl_AAA_Session_tbl_AAA_User_modifier` (`ssnUpdatedBy`),
  KEY `FK_tbl_AAA_Session_tbl_AAA_User` (`ssnUserID`) USING BTREE,
  CONSTRAINT `FK_tbl_AAA_Session_tbl_AAA_User` FOREIGN KEY (`ssnUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Session_tbl_AAA_User_modifier` FOREIGN KEY (`ssnUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_ApprovalRequest` (
  `aprID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aprUserID` bigint unsigned DEFAULT NULL,
  `aprKeyType` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'E:Email, M:Mobile',
  `aprKey` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprCode` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprLastRequestAt` datetime NOT NULL,
  `aprExpireAt` datetime NOT NULL,
  `aprSentAt` datetime DEFAULT NULL,
  `aprApplyAt` datetime DEFAULT NULL,
  `aprStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'N:New, S:Sent, A:Applied, E:Expired',
  `aprCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aprID`),
  KEY `FK_tbl_AAA_ApprovalRequest_tbl_AAA_User` (`aprUserID`),
  CONSTRAINT `FK_tbl_AAA_ApprovalRequest_tbl_AAA_User` FOREIGN KEY (`aprUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_ForgotPasswordRequest` (
  `fprID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fprUserID` bigint unsigned NOT NULL,
  `fprRequestedBy` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'E:Email, M:Mobile',
  `fprCode` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fprLastRequestAt` datetime NOT NULL,
  `fprExpireAt` datetime NOT NULL,
  `fprSentAt` datetime DEFAULT NULL,
  `fprApplyAt` datetime DEFAULT NULL,
  `fprStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'N:New, S:Sent, A:Applied, E:Expired',
  `fprCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fprID`),
  KEY `FK_tbl_AAA_ForgotPasswordRequest_tbl_AAA_User` (`fprUserID`),
  CONSTRAINT `FK_tbl_AAA_ForgotPasswordRequest_tbl_AAA_User` FOREIGN KEY (`fprUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_AlertType` (
  `altID` int unsigned NOT NULL AUTO_INCREMENT,
  `altKey` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `altType` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'E:Email, M:Mobile',
  `altBody` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`altID`),
  UNIQUE KEY `altKey` (`altKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TABLE IF NOT EXISTS `tbl_AAA_Alert` (
  `alrID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alrUserID` bigint unsigned DEFAULT NULL,
  `alrApprovalRequestID` bigint unsigned DEFAULT NULL,
  `alrForgotPasswordRequestID` bigint unsigned DEFAULT NULL,
  `alrTypeKey` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alrTarget` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alrInfo` json NOT NULL,
  `alrLockedAt` datetime DEFAULT NULL,
  `alrLockedBy` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alrLastTryAt` datetime DEFAULT NULL,
  `alrSentAt` datetime DEFAULT NULL,
  `alrResult` json DEFAULT NULL,
  `alrStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'N:New, P:Processing, S:Sent, E:Error, R:Removed',
  `alrCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alrCreatedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`alrID`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_User` (`alrUserID`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_User_creator` (`alrCreatedBy`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_ApprovalRequest` (`alrApprovalRequestID`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_ForgotPasswordRequest` (`alrForgotPasswordRequestID`),
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_ApprovalRequest` FOREIGN KEY (`alrApprovalRequestID`) REFERENCES `tbl_AAA_ApprovalRequest` (`aprID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_ForgotPasswordRequest` FOREIGN KEY (`alrForgotPasswordRequestID`) REFERENCES `tbl_AAA_ForgotPasswordRequest` (`fprID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_User` FOREIGN KEY (`alrUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_User_creator` FOREIGN KEY (`alrCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQLSTR
    );




    //)w,Ps&_2BpKe
//N9qlkkKr)S25

    $this->batchInsertIgnore('{{%Role}}', ['rolID', 'rolName', 'rolParentID', 'rolPrivs'], [
      [ 1, 'Full Access', NULL, '{"*":1}'],
      [10, 'User',        NULL, '{"aaa":{"user":{"login":1,"logout":1}}}'],
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

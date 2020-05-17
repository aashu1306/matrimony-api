CREATE TABLE `np_user_types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `np_user_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_user_postions` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `position` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `np_user_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `study_participant_id` smallint(5) unsigned NOT NULL,
  `np_user_type_id` tinyint(3) unsigned NOT NULL,
  `np_user_position_id` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(115) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pword` varchar(115) COLLATE utf8_unicode_ci NOT NULL,
  `pword_txt` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bp_access` tinyint(3) DEFAULT '0',
  `ecr_access` tinyint(3) DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`,`np_user_type_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_company_user_benchmarks` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_user_id` smallint(5) unsigned NOT NULL,
  `study_participant_id` smallint(5) NOT NULL,
  `benchmark_id` smallint(5) unsigned NOT NULL,
  `primary` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_company_user_demographics` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_user_id` smallint(5) unsigned NOT NULL,
  `study_participant_id` smallint(5) NOT NULL,
  `survey_demographic_id` mediumint(6) unsigned NOT NULL,
  `survey_demographic_option_id` mediumint(6) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_company_user_identifiers` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_user_id` smallint(5) unsigned NOT NULL,
  `study_participant_id` smallint(5) NOT NULL,
  `survey_backend_demographic_id` smallint(5) unsigned NOT NULL,
  `survey_backend_demographic_option_id` smallint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_kpi_colors` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `np_color` (`color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_kpis` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_user_id` smallint(5) unsigned NOT NULL,
  `study_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `np_kpi_color_id` tinyint(3) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_kpi_details` (
  `id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_kpi_id` smallint(5) unsigned NOT NULL,
  `template_rating_master_id` smallint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_login_logs` (
  `id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_user_id` smallint(5) unsigned NOT NULL,
  `np_user_agent` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `ca_category` tinyint(2) DEFAULT NULL,
  `ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_survey_demographic_option_mappings` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` smallint(5) unsigned NOT NULL,
  `survey_demographic_id` mediumint(6) unsigned NOT NULL,
  `survey_demographic_option_id` mediumint(6) unsigned NOT NULL,
  `historical_survey_id` smallint(5) unsigned NOT NULL,
  `historical_survey_demographic_id` mediumint(6) unsigned NOT NULL,
  `historical_survey_demographic_option_id` mediumint(6) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `np_survey_demographic_option` (`survey_id`,`survey_demographic_id`,`survey_demographic_option_id`,`historical_survey_id`,`historical_survey_demographic_id`,`historical_survey_demographic_option_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_template_rating_masters` (
  `id` smallint(5) NOT NULL DEFAULT '0',
  `template_id` smallint(5) unsigned NOT NULL,
  `statement` varchar(255) NOT NULL,
  `dimension_id` smallint(5) unsigned NOT NULL,
  `sub_dimension_id` smallint(5) unsigned NOT NULL,
  `sub_dimension_category_id` smallint(5) unsigned NOT NULL,
  `rating_category_master_id` tinyint(2) unsigned DEFAULT NULL,
  `survey_order` smallint(5) unsigned DEFAULT NULL,
  `report_order` smallint(5) unsigned DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `parent_id` smallint(5) unsigned DEFAULT NULL,
  `common_rating_master_id` smallint(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `np_user_companies` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_user_id` smallint(5) unsigned NOT NULL,
  `company_id` smallint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_email_jobs` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_email_template_id` smallint(5) unsigned NOT NULL,
  `np_email_template_name` varchar(100) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `content` text,
  `total` mediumint(8) unsigned NOT NULL,
  `sent` mediumint(8) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `active` tinyint(3) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `np_email_job_details` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `np_email_job_id` smallint(5) unsigned NOT NULL,
  `np_email_template_id` smallint(5) unsigned NOT NULL,
  `np_email_template_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` smallint(5) unsigned NOT NULL,
  `subject` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `from` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `reply_to` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sender` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `sent_date` timestamp NULL DEFAULT NULL,
  `error_Info` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_email_templates` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(100) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `reply_to` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `attachment` tinyint(3) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `np_email_to_details` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `np_email_job_detail_id` mediumint(6) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `category` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `np_kpi_colors` (`color`) VALUES ('#F38A00');
INSERT INTO `np_kpi_colors` (`color`) VALUES ('#015075');
INSERT INTO `np_kpi_colors` (`color`) VALUES ('#AA272F');
INSERT INTO `np_kpi_colors` (`color`) VALUES ('#6B8D00');
INSERT INTO `np_kpi_colors` (`color`) VALUES ('#612141');
INSERT INTO `np_kpi_colors` (`color`) VALUES ('#9B9B9B');

INSERT INTO `np_user_types` (`id`,`type`) VALUES (1,'admin');
INSERT INTO `np_user_types` (`id`,`type`) VALUES (2,'company_admin');

DROP TABLE `np_template_rating_masters`;
create table np_template_rating_masters select * from template_rating_masters;

INSERT INTO `np_user_postions` (`id`, `position`, `created`, `modified`) VALUES ('1', 'Manager', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

INSERT INTO `np_users` (`id`, `study_participant_id`, `np_user_type_id`, `np_user_position_id`, `name`, `email`, `pword`, `pword_txt`, `phone`, `image_name`, `bp_access`, `ecr_access`, `active`, `created`, `modified`) VALUES ('1', '0', '1', '1', 'GPIT Admin', 'admin@localhost.com', 'e10adc3949ba59abbe56e057f20f883e', '123456', '0123456789', '', '0', '0', '1', '2016-10-12 12:11:28', '2016-10-12 12:11:28');

ALTER TABLE `np_users` 
ADD COLUMN `photo_url` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `study_participants` 
ADD COLUMN `photo_url` TINYINT(1) NOT NULL DEFAULT 0;

INSERT INTO `api_users` (`id`, `api_type`, `api_username`, `api_password`, `api_url`, `media_bucket_name`, `media_bucket_folder`) VALUES ('', 'chille_nrp_local_amazon_s3', 'pSNRWRJiZ23dIl7xLVyTbd5rquWXF33+XrspGNCXxgg=', '2qF+Z82IcH1qvooRLqEn6EQamKBsg3wTRRJLUGPXpbsZr7Qw+cDvXA6bO7bQ334jy3GDiv9lJqvV1sa6c4Jq9A==', 'gptwqa.s3-website-us-west-2.amazonaws.com', 'qa-reportportal.gpssapp.com', 'chiledocument');
-- Changes by Archee on 29/11
CREATE TABLE `np_languages` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `language` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `language_display` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `language_short_code` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Changes by Archee on 30/11
ALTER TABLE `np_user_types` 
ADD COLUMN `frontend_type` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `np_user_companies` 
ADD COLUMN `photo_url` TINYINT(1) NOT NULL DEFAULT 0;

INSERT INTO `np_email_templates` (`from`, `sender`, `subject`, `name`, `content`, `attachment`, `level`, `active`) VALUES ('GPIT <pravin@greatplaceitservices.com>', 'GPIT User', 'New Password', 'Forget Password', 'Hello <strong>[[name]]</strong>,<br /><strong>URL : </strong>[[url]]<br /><strong>User Name :</strong> [[user_name]]<br /><strong>New Password :</strong> [[new_password]]</p>\\n\\n<p>Thanks,<br /><strong>GPTW Support Team</strong></p>\\n\'', '0', '1', '1');

ALTER TABLE `np_login_logs` 
DROP FOREIGN KEY `np_login_logs_ibfk_1`;
ALTER TABLE `np_login_logs` 
DROP INDEX `user_id` ;

-- Changes by Pravin Sir

ALTER TABLE `practice_category` 
ADD COLUMN `practice_group` VARCHAR(45) NULL;


ALTER TABLE `benchmarks` 
ADD COLUMN `category_id` SMALLINT(5) NULL;


ALTER TABLE `np_users` 
ADD COLUMN `company_id` SMALLINT(5) UNSIGNED NULL;


UPDATE `practice_category` SET `practice_group`='Caring' WHERE `id`='1';
UPDATE `practice_category` SET `practice_group`='Caring' WHERE `id`='2';
UPDATE `practice_category` SET `practice_group`='Caring' WHERE `id`='3';
UPDATE `practice_category` SET `practice_group`='Celebrating' WHERE `id`='4';
UPDATE `practice_category` SET `practice_group`='Developing' WHERE `id`='5';
UPDATE `practice_category` SET `practice_group`='Hiring' WHERE `id`='6';
UPDATE `practice_category` SET `practice_group`='Hiring' WHERE `id`='7';
UPDATE `practice_category` SET `practice_group`='Inspiring' WHERE `id`='8';
UPDATE `practice_category` SET `practice_group`='Listening' WHERE `id`='9';
UPDATE `practice_category` SET `practice_group`='Listening' WHERE `id`='10';
UPDATE `practice_category` SET `practice_group`='Listening' WHERE `id`='11';
UPDATE `practice_category` SET `practice_group`='Sharing' WHERE `id`='13';
UPDATE `practice_category` SET `practice_group`='Sharing' WHERE `id`='14';
UPDATE `practice_category` SET `practice_group`='Speaking' WHERE `id`='15';
UPDATE `practice_category` SET `practice_group`='Thanking' WHERE `id`='16';


ALTER TABLE `study_participants` 
ADD COLUMN `is_report_portal_allowed` TINYINT(1) NULL DEFAULT 0,
ADD COLUMN `portal_allowed_date` TIMESTAMP NULL;

--  Changes by Archee
ALTER TABLE `np_company_user_benchmarks` 
ADD COLUMN `company_id` SMALLINT(5) UNSIGNED NULL;

ALTER TABLE `np_users` 
DROP INDEX `email` ,
ADD UNIQUE INDEX `email` (`np_user_type_id` ASC, `email` ASC, `study_participant_id` ASC);

-- changes by Pravin Sir
ALTER TABLE `study_participants` 
ADD COLUMN `bp_access` TINYINT(3) NULL,
ADD COLUMN `ecr_access` TINYINT(30) NULL;

ALTER TABLE `companies` 
ADD COLUMN `is_logo_uploaded` TINYINT(3) NULL DEFAULT 0;

ALTER TABLE `np_kpis` 
ADD COLUMN `active` tinyint(3) unsigned NOT NULL DEFAULT '1';

ALTER TABLE  `np_kpis` CHANGE active is_deleted tinyint(3) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `np_users` 
ADD COLUMN `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `np_kpis` 
CHANGE COLUMN `is_deleted` `active` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' ;

-------- 22-22-2016 Pravin --------------
ALTER TABLE `practice_category` 
ADD COLUMN `practice_group` VARCHAR(45) NULL;


--------------- 26-12-2016 Pravin --------------
ALTER TABLE `benchmarks` 
ADD COLUMN `category_id` SMALLINT(5) NULL;

---------- 4-1-2017 Prvin ----------------------
ALTER TABLE `np_users` 
ADD COLUMN `company_id` SMALLINT(5) UNSIGNED NULL;

------------- 5-1-2017 Pravin -------------
UPDATE `practice_category` SET `practice_group`='Caring' WHERE `id`='1';
UPDATE `practice_category` SET `practice_group`='Caring' WHERE `id`='2';
UPDATE `practice_category` SET `practice_group`='Caring' WHERE `id`='3';
UPDATE `practice_category` SET `practice_group`='Celebrating' WHERE `id`='4';
UPDATE `practice_category` SET `practice_group`='Developing' WHERE `id`='5';
UPDATE `practice_category` SET `practice_group`='Hiring' WHERE `id`='6';
UPDATE `practice_category` SET `practice_group`='Hiring' WHERE `id`='7';
UPDATE `practice_category` SET `practice_group`='Inspiring' WHERE `id`='8';
UPDATE `practice_category` SET `practice_group`='Listening' WHERE `id`='9';
UPDATE `practice_category` SET `practice_group`='Listening' WHERE `id`='10';
UPDATE `practice_category` SET `practice_group`='Listening' WHERE `id`='11';
UPDATE `practice_category` SET `practice_group`='Sharing' WHERE `id`='13';
UPDATE `practice_category` SET `practice_group`='Sharing' WHERE `id`='14';
UPDATE `practice_category` SET `practice_group`='Speaking' WHERE `id`='15';
UPDATE `practice_category` SET `practice_group`='Thanking' WHERE `id`='16';

----------------- 13-1-2017 Pravin ---------------------
ALTER TABLE `companies` 
ADD COLUMN `is_logo_uploaded` TINYINT(3) NULL DEFAULT 0;

ALTER TABLE `companies` 
ADD COLUMN `bp_access` TINYINT(3) NULL DEFAULT 0;

---------------------- Pravin 11-3-2017 -----------------------------
CREATE TABLE `np_portal_configs` (
  `id` TINYINT NULL AUTO_INCREMENT,
  `widget` VARCHAR(45) NULL,
  `title` VARCHAR(45) NULL,
  `order` TINYINT(3) NULL,
  PRIMARY KEY (`id`));


  CREATE TABLE `np_portal_config_details` (
  `id` TINYINT(3) NOT NULL AUTO_INCREMENT,
  `np_portal_config_id` TINYINT(3) NULL,
  `min_perspective` TINYINT(3) NULL,
  `perspective` TINYINT(3) NULL,
  `display_name` VARCHAR(45) NULL,
  `score_type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`));

INSERT INTO `np_portal_configs` (`widget`, `title`, `order`) VALUES ('gptw_model', 'Workgroup', '1');

---------------------- Pravin 13-3-2017 -------------------
ALTER SCHEMA DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_unicode_ci ;

ALTER TABLE `np_portal_configs` 
ADD COLUMN `type` VARCHAR(45) NULL;


---------------------- Pravin 30-3-2017 ------------------------

CREATE TABLE `np_portal_config_details_other_languages` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `np_portal_config_detail_id` tinyint(3) DEFAULT NULL,
  `display_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language_id` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `np_portal_configs_other_languages` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `np_portal_config_id` tinyint(3) DEFAULT NULL,
  `language_id` tinyint(3) DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Email Template by Archee
INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("1","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","New Password","FrontEnd Forget Password","Hello <strong>[[name]]</strong>,<br />Your <strong>new password</strong> for <strong> loginid </strong> : <u> [[email]] </u> is </strong> [[password]] <br><strong> Visit <strong> https://rp-chile.gpssapp.com/</strong>to access report portal</p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");

INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("2","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Welcome to Report Portal","Front End Login Company Admin ","Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal as Company Admin</strong><br/><strong>url :</strong> https://rp-chile.gpssapp.com/ <br /><strong>LoginId :</strong> [[email]]<br /><strong>New Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");


INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("3","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Welcome to Report Portal"," Admin Portal Invitation","Hello <strong>[[name]]</strong>,<br /><strong><br /><strong>url :</strong> https://rp-chile.gpssapp.com/admin <br /><strong>LoginId :</strong> [[email]]<br /><strong>New Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");

INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("4","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","New Password","Forget Password","Hello <strong>[[name]]</strong>,<br /><strong>Your account password has recently been changed <br/>Your <strong>new password</strong> for <strong> loginid </strong> : <u> [[email]] </u> is </strong> [[password]] <br><strong> Visit <strong> https://rp-chile.gpssapp.com/admin</strong>to access report portal</p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");


INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("5","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Resending Credentials","Admin Invitation Resending","Hello <strong>[[name]]</strong>,<br /><strong>It seems you lost your login credentials.Kindly save these credentials<br /><strong>url :</strong> https://rp-chile.gpssapp.com/admin <br /><strong>LoginId :</strong> [[email]]<br /><strong>New Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");

INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("6","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Resending Credentials","Client Invitation resending","Hello <strong>[[name]]</strong>,<br /><strong>It seems you lost your login credentials.Kindly save these credentials<br /><strong>url :</strong> https://rp-chile.gpssapp.com/ <br /><strong>LoginId :</strong> [[email]]<br /><strong>New Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");

INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("7","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Welcome to Report Portal","Front End Login Leader ","Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal as Leader</strong><br><strong>url :</strong> https://rp-chile.gpssapp.com/ <br /><strong>LoginId :</strong> [[email]]<br /><strong> Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");

INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("8","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Welcome to Report Portal","Front End Login Manager","Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal as Manager</strong><br><strong>url :</strong> https://rp-chile.gpssapp.com/ <br /><strong>LoginId :</strong> [[email]]<br /><strong> Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");


INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("9","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Welcome to Report Portal","Admin Login Consultant","Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal as Consultant</strong><br><strong>url :</strong> https://rp-chile.gpssapp.com/admin <br /><strong>LoginId :</strong> [[email]]<br /><strong> Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");


INSERT INTO `np_email_templates`(`id`,`from`,`sender`,`reply_to`,`subject`,`name`, `content`,`attachment`,`level`,`active`,`created`)VALUES ("10","GPIT <pravin@greatplaceitservices.com>","GPIT User","GPIT <pravin@greatplaceitservices.com>","Welcome to Report Portal","Admin Login Reviewer","Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal as Reviewer</strong><br><strong>url :</strong> https://rp-chile.gpssapp.com/admin <br /><strong>LoginId :</strong> [[email]]<br /><strong> Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>","0","1","1","0000-00-00 00:00:00");


UPDATE `np_email_templates` SET `content`='Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal</strong><br><strong>url :</strong> https://rp-chile.gpssapp.com/ <br /><strong>LoginId :</strong> [[email]]<br /><strong> Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>' WHERE `id`='7';
UPDATE `np_email_templates` SET `content`='Hello <strong>[[name]]</strong>,<br /><strong><br><br /> You are invited to Report portal</strong><br><strong>url :</strong> https://rp-chile.gpssapp.com/ <br /><strong>LoginId :</strong> [[email]]<br /><strong> Password :</strong> [[password]] </p><p>Thanks,<br /><strong>GPTW Support Team</strong></p>' WHERE `id`='8';


ALTER TABLE `languages` 
ADD COLUMN `for_report_portal` TINYINT(2) NULL DEFAULT NULL;


CREATE TABLE `practice_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `practice_group` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `practice_category` 
ADD COLUMN `practice_group_id` INT(11) NOT NULL;
ALTER TABLE `practice_category` 
CHANGE COLUMN `practice_group_id` `practice_group_id` INT(11)  NULL ;

INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('1', 'Caring');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('2', 'Celebrating');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('3', 'Developing');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('4', 'Hiring');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('5', 'Inspiring');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('6', 'Listening');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('7', 'Sharing');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('8', 'Speaking');
INSERT INTO `practice_groups` (`id`, `practice_group`) VALUES ('9', 'Thanking');

UPDATE `practice_category` SET `practice_group_id`='1' WHERE `id`='1';
UPDATE `practice_category` SET `practice_group_id`='1' WHERE `id`='2';
UPDATE `practice_category` SET `practice_group_id`='1' WHERE `id`='3';
UPDATE `practice_category` SET `practice_group_id`='2' WHERE `id`='4';
UPDATE `practice_category` SET `practice_group_id`='3' WHERE `id`='5';
UPDATE `practice_category` SET `practice_group_id`='4' WHERE `id`='6';
UPDATE `practice_category` SET `practice_group_id`='4' WHERE `id`='7';
UPDATE `practice_category` SET `practice_group_id`='5' WHERE `id`='8';
UPDATE `practice_category` SET `practice_group_id`='6' WHERE `id`='9';
UPDATE `practice_category` SET `practice_group_id`='6' WHERE `id`='10';
UPDATE `practice_category` SET `practice_group_id`='6' WHERE `id`='11';
UPDATE `practice_category` SET `practice_group_id`='7' WHERE `id`='13';
UPDATE `practice_category` SET `practice_group_id`='7' WHERE `id`='14';
UPDATE `practice_category` SET `practice_group_id`='8' WHERE `id`='15';
UPDATE `practice_category` SET `practice_group_id`='9' WHERE `id`='16';


CREATE TABLE `practice_group_other_languages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` tinyint(3) DEFAULT NULL,
  `practice_group_id` int(11) not null,
  `display_practice_group` varchar(100) Default Null,
  PRIMARY KEY (`id`)
 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `practice_groups` 
ADD COLUMN `practice_color` VARCHAR(50) NULL;

UPDATE `practice_groups` SET `practice_color`='#005472' WHERE `id`='1';
UPDATE `practice_groups` SET `practice_color`='#9E6490' WHERE `id`='2';
UPDATE `practice_groups` SET `practice_color`='#00B2A5' WHERE `id`='3';
UPDATE `practice_groups` SET `practice_color`='#172C56' WHERE `id`='4';
UPDATE `practice_groups` SET `practice_color`='#FF7B22' WHERE `id`='5';
UPDATE `practice_groups` SET `practice_color`='#EA3D26' WHERE `id`='6';
UPDATE `practice_groups` SET `practice_color`='#8A0917' WHERE `id`='7';
UPDATE `practice_groups` SET `practice_color`='#FDB515' WHERE `id`='8';
UPDATE `practice_groups` SET `practice_color`='#9DAD33' WHERE `id`='9';

INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('1', '2', '1', 'Cuidando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('2', '2', '2', 'Celebrando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('3', '2', '3', 'Desarrollando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('4', '2', '4', 'Contratando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('5', '2', '5', 'Inspirando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('6', '2', '6', 'Escuchando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('7', '2', '7', 'Compartiendo');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('8', '2', '8', 'Hablando');
INSERT INTO `practice_group_other_languages` (`id`, `language_id`, `practice_group_id`, `display_practice_group`) VALUES ('9', '2', '9', 'Agradeciendo');

ALTER TABLE `practice_groups` 
ADD COLUMN `sort_order` TINYINT(2) NULL;

UPDATE `practice_groups` SET `sort_order`='1' WHERE `id`='5';
UPDATE `practice_groups` SET `sort_order`='2' WHERE `id`='8';
UPDATE `practice_groups` SET `sort_order`='3' WHERE `id`='6';
UPDATE `practice_groups` SET `sort_order`='4' WHERE `id`='9';
UPDATE `practice_groups` SET `sort_order`='5' WHERE `id`='3';
UPDATE `practice_groups` SET `sort_order`='6' WHERE `id`='1';
UPDATE `practice_groups` SET `sort_order`='7' WHERE `id`='7';
UPDATE `practice_groups` SET `sort_order`='8' WHERE `id`='2';
UPDATE `practice_groups` SET `sort_order`='9' WHERE `id`='4';

---------------------- Ashutosh 09-06-2017 ------------------------

CREATE TABLE `np_company_user_segments` (
`np_user_id` SMALLINT( 5 ) NOT NULL ,
`study_participant_id` SMALLINT( 5 ) NOT NULL ,
`survey_id` SMALLINT( 3 ) NOT NULL ,
`segment_id` SMALLINT( 5 ) NOT NULL 
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `np_company_user_segments` 
ADD COLUMN `id` MEDIUMINT(8) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

ALTER TABLE `survey_segmentation_details` 
ADD COLUMN `type` TINYINT(3) NOT NULL DEFAULT '0';


CREATE TABLE `np_logs` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` smallint(5) unsigned NOT NULL,
  `company_id` smallint(5) unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` tinyint(2) NOT NULL,
  `date` datetime NOT NULL,
  `ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=831 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--------------Ashutosh  09-11-2017 chile specific--------------
UPDATE `practice_category_statement_mapping` SET `common_rating_master_id`='25', `statement_in_english`='Management does a good job of assigning and coordinating people.' WHERE `common_rating_master_id`='57' and `practice_category_id` = '6';

INSERT INTO `np_portal_configs` (`widget`, `title`, `type`, `order`, `active`) VALUES ('gptw_model', 'Average of perspectives', 'box', '4', '1');

INSERT INTO `np_portal_config_details` (`np_portal_config_id`, `min_perspective`, `perspective`, `display_name`, `score_type`, `order`, `active`) VALUES ('8', '2', '2', 'Average of perspectives', 'average', '4', '1');

INSERT INTO `np_portal_configs_other_languages` (`np_portal_config_id`, `language_id`, `title`) VALUES ('8', '2', 'Promedio entre visiones');

INSERT INTO `np_portal_configs_other_languages` (`np_portal_config_id`, `language_id`, `title`) VALUES ('8', '4', 'Média entre visões');

INSERT INTO `np_portal_config_details_other_languages` (`np_portal_config_detail_id`, `display_name`, `language_id`) VALUES ('13', 'Promedio entre visiones', '2');

INSERT INTO `np_portal_config_details_other_languages` (`np_portal_config_detail_id`, `display_name`, `language_id`) VALUES ('13', 'Média entre visões', '4');

ALTER TABLE `study_participants` 
ADD COLUMN `historic_access` TINYINT(30) NOT NULL DEFAULT '1';


CREATE TABLE `segment_mappings` (
  `id` SMALLINT(5) NOT NULL AUTO_INCREMENT,
  `study_id` TINYINT(3) NOT NULL,
  `historic_study_id` TINYINT(3) NOT NULL,
  `segment_id` SMALLINT(5) NOT NULL,
  `historic_segment_id` SMALLINT(5) NOT NULL,
  `company_id` SMALLINT(5) NOT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `segment_targets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `study_id` tinyint(3) unsigned NOT NULL,
  `segment_id` smallint(5) unsigned DEFAULT NULL,
  `org_target` smallint(5) DEFAULT NULL,
  `wkg_target` smallint(5) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `segment_target_study` (`study_id`),
  KEY `segment_target_survey-segment` (`segment_id`),
  CONSTRAINT `segment_target_study` FOREIGN KEY (`study_id`) REFERENCES `studies` (`id`),
  CONSTRAINT `segment_target_survey-segment` FOREIGN KEY (`segment_id`) REFERENCES `survey_segmentations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=308 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `chile`.`np_portal_config_details` (`np_portal_config_id`, `min_perspective`, `perspective`, `score_type`, `order`, `active`) VALUES ('1', '2', '2', 'target', '4', '1');
INSERT INTO `chile`.`np_portal_config_details` (`np_portal_config_id`, `min_perspective`, `perspective`, `score_type`, `order`, `active`) VALUES ('2', '1', '1', 'target', '4', '1');

CREATE TABLE `organizational_targets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `study_id` tinyint(3) unsigned NOT NULL,
  `company_id` smallint(5) unsigned DEFAULT NULL,
  `org_target` smallint(5) DEFAULT NULL,
  `wkg_target` smallint(5) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `organizational_targets_study` (`study_id`),
  KEY `organizational_targets_company_study` (`company_id`),
  CONSTRAINT `organizational_targets_study` FOREIGN KEY (`study_id`) REFERENCES `studies` (`id`),
  CONSTRAINT `organizational_targets_survey-segment` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=308 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `organizational_targets` 
CHANGE COLUMN `org_target` `org_target` FLOAT NULL DEFAULT NULL ,
CHANGE COLUMN `wkg_target` `wkg_target` FLOAT NULL DEFAULT NULL ;
ALTER TABLE `segment_targets` 
CHANGE COLUMN `org_target` `org_target` FLOAT NULL DEFAULT NULL ,
CHANGE COLUMN `wkg_target` `wkg_target` FLOAT NULL DEFAULT NULL ;

---------------------changes for REQ-4083-----------------------
ALTER TABLE `np_company_user_benchmarks` 
ADD COLUMN `np_user_type_id` TINYINT(3) NOT NULL;

CREATE TABLE `email_statuses_mandrills` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `email_to` varchar(100) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `open` mediumint(8) NOT NULL,
  `click` mediumint(8) NOT NULL DEFAULT '0',
  `event_ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `response_data` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*
MNC
 */

CREATE TABLE `roles` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('1', 'admin', 'Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('2', 'company_admin', 'Company Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('3', 'group_company_admin', 'Grouo Company Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('4', 'group_gptw_admin', 'Group GPTW Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('5', 'evaluator_admin', 'Evaluator Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('6', 'evaluator_user', 'Evaluator User');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('7', 'rp_admin', 'Report Portal Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('8', 'rp_consultant', 'Consultant');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('9', 'rp_reviewer', 'Reviewer');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('10', 'rp_company_admin', 'Report Portal Company Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('11', 'rp_group_company_admin', 'Report Portal Group Admin');
INSERT INTO `roles` (`id`, `role`, `display_name`) VALUES ('12', 'rp_segment_leader', 'Segment_leader');

CREATE TABLE `users_roles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` smallint(5) unsigned NOT NULL,
  `role_id` tinyint(3) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fsk_users_idx` (`user_id`),
  KEY `fsk_roles_idx` (`role_id`),
  CONSTRAINT `fsk_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fsk_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `np_company_user_segments` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

ALTER TABLE `np_company_user_benchmarks` 
ADD COLUMN `user_id` SMALLINT(5) UNSIGNED NOT NULL AFTER `np_user_id`,
ADD INDEX `user_id` (`user_id`),
ADD INDEX `np_company__user_benchmarks_ibfk_1_idx` (`user_id` ASC);
ALTER TABLE  `np_company_user_benchmarks` 
ADD CONSTRAINT `np_company__user_benchmarks_ibfk_1`
  FOREIGN KEY (`user_id`)
  REFERENCES  `users` (`id`)
  ON DELETE RESTRICT
  ON UPDATE NO ACTION;

ALTER TABLE `np_company_user_benchmarks` 
ADD COLUMN `role_id` TINYINT(3) NOT NULL AFTER `np_user_type_id`;


ALTER TABLE `roles` CHANGE `created` `created` TIMESTAMP NOT NULL, CHANGE `modified` `modified` TIMESTAMP NOT NULL;

ALTER TABLE `users_roles` CHANGE `created` `created` TIMESTAMP NOT NULL, CHANGE `modified` `modified` TIMESTAMP NOT NULL;

UPDATE `roles` SET `display_name`='GPTW Admin' WHERE `id`='1';

UPDATE `roles` SET `display_name`='Group Company Admin' WHERE `id`='3';

CREATE TABLE `users_roles_email_templates` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` smallint(5) unsigned NOT NULL,
  `role_id` tinyint(3) unsigned NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fsk_template_id_idx` (`template_id`),
  KEY `fsk_user_roles_idx` (`role_id`),
  CONSTRAINT `fsk_user_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fsk_templates` FOREIGN KEY (`template_id`) REFERENCES `np_email_templates` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `users_roles_email_templates` (`template_id`, `role_id`) VALUES ('3', '7');
INSERT INTO `users_roles_email_templates` (`template_id`, `role_id`) VALUES ('9', '9');
INSERT INTO `users_roles_email_templates` (`template_id`, `role_id`) VALUES ('10', '8');
INSERT INTO `users_roles_email_templates` (`template_id`, `role_id`) VALUES ('2', '10');
INSERT INTO `users_roles_email_templates` (`template_id`, `role_id`) VALUES ('7', '12');

ALTER TABLE  `np_company_user_benchmarks` 
DROP INDEX `np_company__user_benchmarks_ibfk_1_idx` ;

ALTER TABLE  `users` 
ADD COLUMN `photo_url` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_evaluator_admin`;

ALTER TABLE `np_login_logs` 
DROP FOREIGN KEY `np_login_logs_ibfk_1`;
ALTER TABLE `np_login_logs` 
DROP INDEX `user_id` ;


ALTER TABLE `np_users` 
ADD COLUMN `user_id` SMALLINT(5) NULL DEFAULT 0 ;

ALTER TABLE `np_users` 
ADD COLUMN `updated_flag` TINYINT(1) NULL DEFAULT 0 AFTER `user_id`;

ALTER TABLE `np_users` 
ADD COLUMN `roles_flag` TINYINT(1) NULL DEFAULT 0 AFTER `user_id`;

ALTER TABLE `np_kpis` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

ALTER TABLE `np_login_logs` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `id`;

ALTER TABLE `surveys` 
ADD COLUMN `wordcloud_status` TINYINT(2) NOT NULL DEFAULT '0';

ALTER TABLE `survey_segmentations` 
ADD COLUMN `wordcloud_status` TINYINT(2) NOT NULL DEFAULT '0';

ALTER TABLE `survey_segmentations` 
ADD COLUMN `wordcloud_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `surveys` 
ADD COLUMN `wordcloud_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `surveys` 
ADD COLUMN `is_comments_available` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'If it is \'0\' means comments available and it it is \'1\' means comments not available.';

ALTER TABLE `survey_segmentations` 
ADD COLUMN `is_comments_available` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'If it is \'0\' means comments available and it it is \'1\' means comments not available.';

INSERT INTO `configurations` (`constant`, `value`, `description`) VALUES ('COUNTRY', 'Chile', 'This value used in wordcloud API in Report Portal');

----------------------------07-01-2019 change np_user_type_id to role_id in np_user_benchmarks-------------------

ALTER TABLE `np_company_user_benchmarks`
ADD COLUMN `role_id` TINYINT(3) UNSIGNED NOT NULL;
update np_company_user_benchmarks set role_id = 0;
update np_company_user_benchmarks set role_id = 12 where np_user_type_id = 5;

ALTER TABLE `np_kpis` 
ADD COLUMN `template_id` SMALLINT(5) UNSIGNED NOT NULL;

UPDATE `np_kpis` SET `template_id`=1;



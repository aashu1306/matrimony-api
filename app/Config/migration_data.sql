ALTER TABLE `users` ADD COLUMN `old_id` SMALLINT(5) NULL DEFAULT 0 ;

ALTER TABLE  `users` 
ADD COLUMN `group_company_id` SMALLINT(5) NULL DEFAULT 0;

CREATE TABLE `roles` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `np_user_type_id` tinyint(3) NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL,
  `user_type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `user_companies` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(5) NOT NULL,
  `company_id` mediumint(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2410 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `users_roles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` smallint(5) unsigned NOT NULL,
  `role_id` tinyint(3) unsigned NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`role_id`),
  KEY `fsk_users_idx` (`user_id`),
  KEY `fsk_roles_idx` (`role_id`),
  CONSTRAINT `fsk_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fsk_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2947 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (1,'admin','GPTW Admin',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','admin');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (2,'company_admin','Company Admin',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','Company Admin');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (3,'group_company_admin','Group Company Admin',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','Group Company Admin');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (4,'group_gptw_admin','Group GPTW Admin',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','Group GPTW');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (5,'evaluator_admin','Evaluator Admin',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','Evaluator Admin');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (6,'evaluator_user','Evaluator User',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','Evaluator User');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (7,'rp_admin','Report Portal Admin',1,'0000-00-00 00:00:00','0000-00-00 00:00:00','');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (8,'rp_consultant','Consultant',3,'0000-00-00 00:00:00','0000-00-00 00:00:00','');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (9,'rp_reviewer','Reviewer',4,'0000-00-00 00:00:00','0000-00-00 00:00:00','');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (10,'rp_company_admin','Report Portal Company Admin',2,'0000-00-00 00:00:00','0000-00-00 00:00:00','');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (11,'rp_group_company_admin','Report Portal Group Company Admin',0,'0000-00-00 00:00:00','0000-00-00 00:00:00','');
INSERT INTO `roles` (`id`,`role`,`display_name`,`np_user_type_id`,`created`,`modified`,`user_type`) VALUES (12,'rp_segment_leader','Segment_leader',5,'0000-00-00 00:00:00','0000-00-00 00:00:00','');

update users u join (select min(id) as id, email from users group by email) a on a.email=u.email set u.old_id = a.id;

INSERT INTO `users_roles` (`user_id`,`role_id`) 
select users.old_id,roles.id from users join roles on users.type = roles.user_type where NULLIF(type, '') IS NOT NULL group by old_id,roles.id;

INSERT INTO `user_companies` (`user_id`,`company_id`) 
select users.old_id,users.company_id from users where NULLIF(type, '') IS NOT NULL group by old_id,roles.id;

INSERT INTO `user_companies` (`user_id`, `company_id`) select users.old_id,users.company_id from users where NOT EXISTS(select id from user_companies uc WHERE users.old_id = uc.user_id and users.company_id = uc.company_id);

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

ALTER TABLE `survey_reports` 
CHANGE COLUMN `survey_id` `survey_id` VARCHAR(100) NOT NULL ,
CHANGE COLUMN `company_id` `company_id` VARCHAR(100) NOT NULL ;

ALTER TABLE `logs` 
CHANGE COLUMN `company_id` `company_id` VARCHAR(100) NOT NULL ;

INSERT INTO `master_emails` (`template_id`, `name`, `from`, `reply_to`, `subject`, `content`, `active`, `type`)SELECT `template_id`, 'Login details to user', `from`, `reply_to`, `subject`, `content`, `active`, `type` from `master_emails` where name='Login details to client';

#get all np_users that are unique and not present in users tables
select np.email from np_users np left join users u on np.email = u.email where u.email is null group by np.email;

#insert np_users to users
INSERT INTO `users` (`company_id`,`email`,`pword`,`name`,`phone`,`active`,`created`,`modified`) select np.company_id,np.email,np.pword,np.name, np.phone, np.active, np.created, np.modified from np_users np left join users u on np.email = u.email where u.email is null and np.email is not null group by np.email;

#add user_id column in np_users table
ALTER TABLE `np_users` ADD COLUMN `user_id` SMALLINT(5) NULL DEFAULT 0 ;

#update user_id in np_users table
UPDATE np_users np JOIN users u ON np.email = u.email SET  np.user_id = u.id;

#add user_id column in np_kpis table
ALTER TABLE `np_kpis` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#update user_id in np_kpis table
UPDATE  np_kpis k  join np_users np on k.np_user_id = np.id   SET  k.user_id = np.user_id;

#update user companies																				
ALTER TABLE `np_user_companies` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#add constraint
ALTER TABLE  `np_company_user_benchmarks` 
ADD CONSTRAINT `np_company__user_benchmarks_ibfk_1`
  FOREIGN KEY (`user_id`)
  REFERENCES  `users` (`id`)
  ON DELETE RESTRICT
  ON UPDATE NO ACTION;

#update np_user_companies with user_id
UPDATE np_user_companies nc JOIN np_users np ON nc.np_user_id = np.id SET  nc.user_id = np.user_id;

#insert data in user_companies
INSERT INTO `user_companies` (`company_id`,`user_id`) select company_id , user_id from np_user_companies where company_id != 0;

#change column user_id column to np_user_id
ALTER TABLE `np_email_jobs` 
CHANGE COLUMN `user_id` `np_user_id` SMALLINT(5) UNSIGNED NOT NULL ;

#add user_id column
ALTER TABLE `np_email_jobs` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#update user_id in np_email_jobs
UPDATE np_email_jobs e JOIN np_users np ON e.np_user_id = np.id SET  e.user_id = np.user_id;

#add user_id column in np_company_user_benchmarks table
ALTER TABLE `np_company_user_benchmarks` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#drop np_user index
ALTER TABLE  `np_company_user_benchmarks` 
DROP INDEX `np_company__user_benchmarks_ibfk_1_idx` ;

#update user_id in np_company_user_benchmarks
UPDATE np_company_user_benchmarks nc JOIN np_users np ON nc.np_user_id = np.id SET  nc.user_id = np.user_id;

#add user_id column in np_company_user_benchmarks table
ALTER TABLE `np_company_user_identifiers` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#update user_id in np_company_user_benchmarks
UPDATE np_company_user_identifiers nc JOIN np_users np ON nc.np_user_id = np.id SET  nc.user_id = np.user_id;

#add user_id column in np_company_user_segments table
ALTER TABLE `np_company_user_segments` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#update user_id in np_company_user_segments
UPDATE np_company_user_segments nc JOIN np_users np ON nc.np_user_id = np.id SET  nc.user_id = np.user_id;

#add column to get store previous user_type_id
ALTER TABLE `roles` ADD `np_user_type_id` tinyint(3) NOT NULL AFTER `display_name`;

#update user_type_id according to the role
UPDATE `roles` SET `np_user_type_id`='1' WHERE `id`='7';
UPDATE `roles` SET `np_user_type_id`='3' WHERE `id`='8';
UPDATE `roles` SET `np_user_type_id`='4' WHERE `id`='9';
UPDATE `roles` SET `np_user_type_id`='2' WHERE `id`='10';
UPDATE `roles` SET `np_user_type_id`='5' WHERE `id`='12';

#add table and insert data
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
INSERT INTO `users_roles_email_templates` (`template_id`, `role_id`) VALUES ('7', '12')

#insert data in users_roles
INSERT INTO `users_roles` (`user_id`,`role_id`) select  np.user_id, r.id from roles r  JOIN np_users np ON r.np_user_type_id = np.np_user_type_id where user_id <> 0 and r.np_user_type_id <> 0 group by user_id, r.id;

ALTER TABLE `users_roles` 
ADD UNIQUE INDEX `fsk_users_roles_idx` (`user_id` ASC, `role_id` ASC);

#add user_id column in np_login_logs table
ALTER TABLE `np_login_logs` 
ADD COLUMN `user_id` SMALLINT(5) NOT NULL AFTER `np_user_id`;

#drop older foriegnkey ref
ALTER TABLE `np_login_logs` 
DROP FOREIGN KEY `np_login_logs_ibfk_1`;
ALTER TABLE `np_login_logs` 
DROP INDEX `user_id` ;

#update user_id in np_company_user_benchmarks
UPDATE np_login_logs nc JOIN np_users np ON nc.np_user_id = np.id SET  nc.user_id = np.user_id;


ALTER TABLE `np_company_user_benchmarks` 
DROP FOREIGN KEY `np_company_user_benchmarks_ibfk_1`;
ALTER TABLE `np_company_user_benchmarks` 
ADD INDEX `np_company_user_benchmarks_ibfk_1_idx` (`np_user_id` ASC),
DROP INDEX `np_user_id` ;
ALTER TABLE `np_company_user_benchmarks` 
CHANGE COLUMN `user_id` `user_id` SMALLINT(5) UNSIGNED NOT NULL ;

ALTER TABLE `np_company_user_benchmarks` 
ADD CONSTRAINT `np_company_user_benchmarks_ibfk_1`
  FOREIGN KEY (`user_id`)
  REFERENCES `users` (`id`);


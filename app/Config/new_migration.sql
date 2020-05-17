select np.email from np_users np left join users u on np.email = u.email where u.email is null group by np.email;

#add photo_url
ALTER TABLE `users` 
ADD COLUMN `photo_url` TINYINT(1) NOT NULL DEFAULT 0 AFTER `old_id`;

#insert np_users to users
INSERT INTO `users` (`company_id`,`email`,`pword`,`name`,`phone`,`active`,`created`,`modified`) select np.company_id,np.email,np.pword,np.name, np.phone, np.active, np.created, np.modified from np_users np left join users u on np.email = u.email where u.email is null and np.email is not null group by np.email;

#add user_id column in np_users table
ALTER TABLE `np_users` ADD COLUMN `user_id` SMALLINT(5) NULL DEFAULT 0 ;

#update user_id in np_users table
UPDATE np_users np JOIN users u ON np.email = u.email SET  np.user_id = u.id;

#update photo_url in users table
UPDATE users u JOIN np_users np ON np.user_id = u.id SET  u.photo_url = np.photo_url;

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

#remove duplicate entry from  np_user_companies 
ALTER IGNORE TABLE np_user_companies  ADD UNIQUE (user_id,company_id);

#remove duplicate entry from  np_user_companies 
ALTER IGNORE TABLE user_companies  ADD UNIQUE (user_id,company_id);

#insert data in user_companies
INSERT INTO `user_companies` (`company_id`,`user_id`) select company_id , user_id from np_user_companies npc where company_id != 0 and user_id != 0 AND NOT EXISTS(select id from user_companies uc WHERE npc.user_id = uc.user_id and npc.company_id = uc.company_id) ;

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

#insert data in users_roles
INSERT INTO `users_roles` (`user_id`,`role_id`) select  np.user_id, r.id from roles r  JOIN np_users np ON r.np_user_type_id = np.np_user_type_id where user_id <> 0 and r.np_user_type_id <> 0 group by user_id, r.id;


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
DROP INDEX `np_company_user_benchmarks_ibfk_1_idx` ,
ADD INDEX `np_company_user_benchmarks_ibfk_1_idx` (`user_id` ASC);

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


INSERT INTO `master_emails` (`template_id`, `name`, `from`, `reply_to`, `subject`, `content`, `active`, `type`)SELECT `template_id`, 'Login details to user', `from`, `reply_to`, `subject`, `content`, `active`, `type` from `master_emails` where name='Login details to client';

INSERT INTO `master_emails` (`template_id`, `name`, `from`, `reply_to`, `subject`, `content`, `active`, `type`)SELECT `template_id`, 'New login details to user', `from`, `reply_to`, `subject`, `content`, `active`, `type` from `master_emails` where name='Login details to user';


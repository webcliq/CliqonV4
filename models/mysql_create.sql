# Enter your database name
USE cliqon_dev;
CREATE TABLE `dbitem` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_reference` VARCHAR(255) NOT NULL DEFAULT 'str(0)',
  `c_type` VARCHAR(255) NOT NULL DEFAULT 'string',
  `c_category` VARCHAR(255) NOT NULL DEFAULT 'other',
  `c_common` VARCHAR(255),
  `c_level` VARCHAR(255) NOT NULL DEFAULT '50:50:50',
  `c_order` VARCHAR(255)  DEFAULT 'zz',
  `c_parent` VARCHAR(255) DEFAULT '0',
  `c_document` LONGTEXT NOT NULL,
  `c_options` VARCHAR(255),
  `c_version` VARCHAR(255) NOT NULL DEFAULT '0',
  `c_status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));

CREATE TABLE `dbarchive` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_reference` VARCHAR(255) NOT NULL DEFAULT 'str(0)',
  `c_type` VARCHAR(255) NOT NULL DEFAULT 'string',
  `c_category` VARCHAR(255) NOT NULL DEFAULT 'other',
  `c_common` VARCHAR(255),
  `c_level` VARCHAR(255) NOT NULL DEFAULT '50:50:50',
  `c_order` VARCHAR(255)  DEFAULT 'zz',
  `c_parent` VARCHAR(255) DEFAULT '0',
  `c_document` LONGTEXT NOT NULL,
  `c_options` VARCHAR(255),
  `c_version` VARCHAR(255) NOT NULL DEFAULT '0',
  `c_status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));
  
CREATE TABLE `dbcollection` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_reference` VARCHAR(255) NOT NULL DEFAULT 'str(0)',
  `c_type` VARCHAR(255) NOT NULL DEFAULT 'string',
  `c_category` VARCHAR(255) NOT NULL DEFAULT 'other',
  `c_common` VARCHAR(255),
  `c_level` VARCHAR(255) NOT NULL DEFAULT '50:50:50',
  `c_order` VARCHAR(255)  DEFAULT 'zz',
  `c_parent` VARCHAR(255) DEFAULT '0',
  `c_document` LONGTEXT NOT NULL,
  `c_options` VARCHAR(255),
  `c_revision` VARCHAR(255) DEFAULT '0',
  `c_status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));
  
CREATE TABLE `dbuser` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_username` VARCHAR(255) NOT NULL DEFAULT 'str(0)',
  `c_password` VARCHAR(255) NOT NULL,
  `c_email` VARCHAR(255) NOT NULL DEFAULT 'other',
  `c_group` VARCHAR(255),
  `c_level` VARCHAR(255) NOT NULL DEFAULT '50:50:50',
  `c_document` LONGTEXT NOT NULL,
  `c_options` VARCHAR(255),
  `c_status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));
 
CREATE TABLE `dbsession` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_reference` VARCHAR(255) NOT NULL,
  `c_type` VARCHAR(255) NOT NULL,
  `c_datavalue` VARCHAR(255) NOT NULL,
  `c_access` VARCHAR(255),
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));

CREATE TABLE `dblog` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_reference` VARCHAR(255) NOT NULL,
  `c_category` VARCHAR(255),
  `c_text` VARCHAR(255),
  `c_value` VARCHAR(255),
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));

  
  CREATE TABLE `dbindex` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `c_reference` VARCHAR(255) NOT NULL,
  `c_category` VARCHAR(255),
  `c_text` VARCHAR(255),
  `c_value` VARCHAR(255),
  `c_lastmodified` VARCHAR(255) NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `c_notes` LONGTEXT,
  PRIMARY KEY (`id`));


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	firstname varchar(50) NOT NULL default '',
	tx_weetypogento_id int(11) default '0'
);

#
# Table structure for table 'sys_language'
#
CREATE TABLE sys_language (
	tx_weetypogento_store varchar(255) NOT NULL default '',
);

#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tx_weetypogento_group int(11) default '0',
);

#
# TABLE STRUCTURE FOR TABLE 'tx_weetypogento_cache'
#
CREATE TABLE tx_weetypogento_cache (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	identifier VARCHAR(250) DEFAULT '' NOT NULL,
	crdate INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	content mediumblob,
	lifetime INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier)
) ENGINE=InnoDB;
 
#
# TABLE STRUCTURE FOR TABLE 'tx_weetypogento_cache_tags'
#
CREATE TABLE tx_weetypogento_cache_tags (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	identifier VARCHAR(250) DEFAULT '' NOT NULL,
	tag VARCHAR(250) DEFAULT '' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier),
	KEY cache_tag (tag)
) ENGINE=InnoDB;


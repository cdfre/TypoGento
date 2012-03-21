#
# Table structure for table 'fe_users'.
#
CREATE TABLE fe_users (
	date_of_birth int(11) DEFAULT '0' NOT NULL,
	gender int(11) unsigned DEFAULT '99' NOT NULL,
	static_info_country char(3) DEFAULT '' NOT NULL,
	tx_weetypogento_customer int(11) DEFAULT '0',
	KEY customer_id (tx_weetypogento_customer,pid,deleted),
	KEY email (email,pid,deleted)
);

#
# Table structure for table 'sys_language'
#
CREATE TABLE sys_language (
	tx_weetypogento_store varchar(255) DEFAULT '' NOT NULL,
);

#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tx_weetypogento_group int(11) DEFAULT '0',
);

#
# Table structure for table 'tx_weetypogento_replication_links'.
#
CREATE TABLE tx_weetypogento_replication_links (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	disable tinyint(4) unsigned DEFAULT '0' NOT NULL,
	source int(11) unsigned DEFAULT '0' NOT NULL,
	target int(11) unsigned DEFAULT '0' NOT NULL,
	provider tinyint(3) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	UNIQUE KEY replication_link_id (source,target,provider)
	UNIQUE KEY target_id (target,provider),
	UNIQUE KEY source_id (source,provider)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_weetypogento_cache'.
#
CREATE TABLE tx_weetypogento_cache (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(250) DEFAULT '' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	content mediumblob,
	lifetime int(11) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier)
) ENGINE=InnoDB;
 
#
# Table structure for table 'tx_weetypogento_cache_tags'.
#
CREATE TABLE tx_weetypogento_cache_tags (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(250) DEFAULT '' NOT NULL,
	tag varchar(250) DEFAULT '' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier),
	KEY cache_tag (tag)
) ENGINE=InnoDB;


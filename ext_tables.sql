CREATE TABLE sys_tag (
	name tinytext NOT NULL,
	items int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE sys_tag_mm (
    uid int(11) NOT NULL auto_increment,
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	tablenames varchar(255) DEFAULT '' NOT NULL,
	fieldname varchar(255) DEFAULT '' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
	KEY uid_local_foreign (uid_local,uid_foreign),
	KEY uid_foreign_tablefield (uid_foreign,tablenames(40),fieldname(3),sorting_foreign)
);

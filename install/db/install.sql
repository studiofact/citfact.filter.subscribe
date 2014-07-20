CREATE TABLE IF NOT EXISTS b_citfact_filter_subscribe (
  ID int(11) unsigned NOT NULL AUTO_INCREMENT,
  FILTER text NOT NULL,
  ACTIVE char(1) NOT NULL DEFAULT 'N',
  IBLOCK_ID int(11) NOT NULL,
  SECTION_ID int(11) NULL,
  TIMESTAMP_X datetime,
  PRIMARY KEY (ID)
);

CREATE TABLE IF NOT EXISTS b_citfact_filter_subscribe_notify (
  ID int(11) unsigned NOT NULL AUTO_INCREMENT,
  FILTER_USER_ID int(11) NOT NULL,
  ELEMENT_ID int(11) NOT NULL,
  PRIMARY KEY (ID)
);

CREATE TABLE IF NOT EXISTS b_citfact_filter_subscribe_stack (
  ID int(11) unsigned NOT NULL AUTO_INCREMENT,
  FILTER_USER_ID int(11) NOT NULL,
  ACTION varchar(255) NOT NULL,
  PRIMARY KEY (ID)
);

CREATE TABLE IF NOT EXISTS b_citfact_filter_subscribe_user (
  ID int(11) unsigned NOT NULL AUTO_INCREMENT,
  USER_ID int(11) NOT NULL,
  FILTER_ID int(11) NOT NULL,
  PRIMARY KEY (ID)
);
create table b_forum (
   ID int(10) not null auto_increment,
   NAME varchar(255) not null,
   DESCRIPTION text,
   SORT int(10) not null default '150',
   ACTIVE char(1) not null default 'Y',
   ALLOW_HTML char(1) not null default 'N',
   ALLOW_ANCHOR char(1) not null default 'Y',
   ALLOW_BIU char(1) not null default 'Y',
   ALLOW_IMG char(1) not null default 'Y',
   ALLOW_LIST char(1) not null default 'Y',
   ALLOW_QUOTE char(1) not null default 'Y',
   ALLOW_CODE char(1) not null default 'Y',
   ALLOW_FONT char(1) not null default 'Y',
   ALLOW_SMILES char(1) not null default 'Y',
   ALLOW_UPLOAD char(1) not null default 'N',
   ALLOW_MOVE_TOPIC char(1) not null default 'Y',
   MODERATION char(1) not null default 'N',
   ORDER_BY char(1) not null default 'P',
   ORDER_DIRECTION varchar(4) not null default 'DESC',
   LID char(2) not null default 'ru',
   TOPICS int(11) not null default '0',
   POSTS int(11) not null default '0',
   LAST_POSTER_ID int(11),
   LAST_POSTER_NAME varchar(255),
   LAST_POST_DATE datetime,
   LAST_MESSAGE_ID int(11),
   EVENT1 varchar(255) default 'forum',
   EVENT2 varchar(255) default 'message',
   EVENT3 varchar(255),
   ALLOW_NL2BR char(1) not null default 'N',
   ALLOW_KEEP_AMP char(1) not null default 'N',
   PATH2FORUM_MESSAGE varchar(255) null,
	ALLOW_UPLOAD_EXT varchar(255) null,
	FORUM_GROUP_ID int null,
	ASK_GUEST_EMAIL char(1) not null default 'N',
	XML_ID varchar(255),
	USE_CAPTCHA char(1) not null default 'N',
	HTML varchar(255),
	primary key (ID),
	index IX_FORUM_SORT(SORT),
	index IX_FORUM_ACTIVE(ACTIVE),
	index IX_FORUM_GROUP_ID(FORUM_GROUP_ID)
);

create table b_forum_topic (
	ID bigint(20) not null auto_increment,
	TITLE varchar(255) not null,
	TAGS varchar(255),
	DESCRIPTION varchar(255),
	STATE char(1) not null default 'Y',
	USER_START_ID int(10),
	USER_START_NAME varchar(255),
	START_DATE datetime not null,
	ICON_ID tinyint(2),
	POSTS int(10) not null default '0',
	VIEWS int(10) not null default '0',
	FORUM_ID int(10) not null,
	TOPIC_ID bigint(20),
	APPROVED char(1) not null default 'Y',
	SORT int(10) not null default '150',
	LAST_POSTER_ID int(10),
	LAST_POSTER_NAME varchar(255) not null,
	LAST_POST_DATE datetime not null,
	LAST_MESSAGE_ID bigint(20) null,
	XML_ID varchar(255) null,
	HTML text,
	primary key (ID),
	index IX_FORUM_TOPIC_FORUM(FORUM_ID, APPROVED),
	index IX_FORUM_TOPIC_APPROVED(APPROVED),
	index IX_FORUM_TOPIC_LAST_POST_DATE(LAST_POST_DATE),
	index IX_FORUM_TOPIC_USER_START_ID(USER_START_ID)
);

create table b_forum_message (
	ID bigint(20) not null auto_increment,
	AUTHOR_ID int(10) null,
	AUTHOR_NAME varchar(255) null,
	AUTHOR_EMAIL varchar(255) null,
	AUTHOR_IP varchar(255) null,
	USE_SMILES char(1) not null default 'Y',
	POST_DATE datetime not null,
	POST_MESSAGE text,
	POST_MESSAGE_HTML text,
	POST_MESSAGE_FILTER text,
	EDITOR_ID int(10) null,
	EDITOR_NAME varchar(255) null,
	EDITOR_EMAIL varchar(255) null,
	EDIT_REASON text null,
	EDIT_DATE datetime null,
	FORUM_ID int(10) not null,
	TOPIC_ID bigint(20) not null,
	ATTACH_HITS int(10) not null default '0',
	ATTACH_TYPE varchar(255) null,
	ATTACH_FILE varchar(255) null,
	NEW_TOPIC char(1) not null default 'N',
	APPROVED char(1) not null default 'Y',
	POST_MESSAGE_CHECK char(32),
	GUEST_ID int(10) null,
	AUTHOR_REAL_IP varchar(128) null,
	ATTACH_IMG int null,
	XML_ID varchar(255) NULL,
	PARAM1 varchar(2) NULL,
	PARAM2 int NULL,
	primary key (ID),
	index IX_FORUM_MESSAGE_FORUM(FORUM_ID, APPROVED),
	index IX_FORUM_MESSAGE_TOPIC(TOPIC_ID),
	index IX_FORUM_MESSAGE_AUTHOR(AUTHOR_ID, APPROVED, FORUM_ID),
	index IX_FORUM_MESSAGE_APPROVED(APPROVED)
);

create table b_forum_smile (
	ID smallint(3) not null auto_increment,
	TYPE char(1) not null default 'S',
	TYPING varchar(100) null,
	IMAGE varchar(255) not null,
	DESCRIPTION varchar(255) null,
	CLICKABLE char(1) not null default 'Y',
	SORT int(10) not null default '150',
	IMAGE_WIDTH int not null default '0',
	IMAGE_HEIGHT int not null default '0',
	primary key (ID)
);

create table b_forum_smile_lang
(
  ID int not null auto_increment,
  SMILE_ID int not null,
  LID char(2) not null,
  NAME varchar(255) not null,
  primary key (ID),
  unique UX_FORUM_SMILE_K(SMILE_ID, LID)
);

create table b_forum_user (
	ID bigint(10) not null auto_increment,
	USER_ID int(10)not null,
	ALIAS varchar(64) null,
	DESCRIPTION varchar(255) null,
	IP_ADDRESS varchar(128) null,
	AVATAR int(10),
	NUM_POSTS int(10) default '0',
	INTERESTS text,
	LAST_POST int(10),
	ALLOW_POST char(1) not null default 'Y',
	LAST_VISIT datetime not null,
	DATE_REG date not null,
	REAL_IP_ADDRESS varchar(128) null,
	SIGNATURE varchar(255) null,
	SHOW_NAME char(1) not null default 'Y',
	RANK_ID int null,
	POINTS int not null default 0,
	HIDE_FROM_ONLINE char(1) not null default 'N',
	SUBSC_GROUP_MESSAGE char(1) NOT NULL default 'N',
	SUBSC_GET_MY_MESSAGE char(1) NOT NULL default 'Y',
	primary key (ID),
	unique IX_FORUM_USER_USER6(USER_ID)
);

create table b_forum_perms
(
	ID int not null auto_increment,
	FORUM_ID int not null,
	GROUP_ID int not null,
	PERMISSION char(1) not null default 'M',
	primary key (ID),
	index IX_FORUM_PERMS_FORUM(FORUM_ID, GROUP_ID),
	index IX_FORUM_PERMS_GROUP(GROUP_ID)
);

create table b_forum_subscribe (
	ID int(10) not null auto_increment,
	USER_ID int(10) not null,
	FORUM_ID int(10) not null,
	TOPIC_ID int(10) null,
	START_DATE datetime not null,
	LAST_SEND int(10) null,
	NEW_TOPIC_ONLY char(50) not null default 'N',
	SITE_ID char(2) not null default 'ru',
	primary key (ID),
	unique UX_FORUM_SUBSCRIBE_USER(USER_ID, FORUM_ID, TOPIC_ID)
);

create table b_forum_rank
(
  ID int not null auto_increment,
  CODE varchar(100) null,
  MIN_NUM_POSTS int not null default 0,
  primary key (ID)
);

create table b_forum_rank_lang
(
  ID int not null auto_increment,
  RANK_ID int not null,
  LID char(2) not null,
  NAME varchar(100) not null,
  primary key (ID),
  unique UX_FORUM_RANK(RANK_ID, LID)
);

create table b_forum_group
(
  ID int not null auto_increment,
  SORT int not null default '150',
  XML_ID varchar(255) NULL,
  primary key (ID)
);

create table b_forum_group_lang
(
  ID int not null auto_increment,
  FORUM_GROUP_ID int not null,
  LID char(2) not null,
  NAME varchar(255) not null,
  DESCRIPTION varchar(255) null,
  primary key (ID),
  unique UX_FORUM_GROUP(FORUM_GROUP_ID, LID)
);

CREATE TABLE b_forum_points
(
  ID int not null auto_increment,
  MIN_POINTS int not null,
  CODE varchar(100) null,
  VOTES int not null,
  primary key (ID),
  unique UX_FORUM_P_MP(MIN_POINTS)
);

CREATE TABLE b_forum_points_lang
(
  POINTS_ID int not null,
  LID char(2) not null,
  NAME varchar(250) null,
  primary key (POINTS_ID, LID)
);

CREATE TABLE b_forum_points2post
(
  ID int not null auto_increment,
  MIN_NUM_POSTS int not null,
  POINTS_PER_POST decimal(18, 4) default 0 not null,
  primary key (ID),
  unique UX_FORUM_P2P_MNP(MIN_NUM_POSTS)
);

CREATE TABLE b_forum_user_points
(
  FROM_USER_ID int not null,
  TO_USER_ID int not null,
  POINTS int default 0 not null,
  DATE_UPDATE datetime null,
  primary key (FROM_USER_ID, TO_USER_ID)
);

CREATE TABLE b_forum2site
(
  FORUM_ID int not null,
  SITE_ID char(2) not null,
  PATH2FORUM_MESSAGE varchar(250) null,
  primary key (FORUM_ID, SITE_ID)
);
CREATE TABLE b_forum_private_message (
  ID BIGINT(10) NOT NULL AUTO_INCREMENT,
  AUTHOR_ID INT(11) DEFAULT '0',
  RECIPIENT_ID INT(11) DEFAULT '0',
  POST_DATE DATETIME NOT NULL,
  POST_SUBJ VARCHAR(50) NOT NULL,
  POST_MESSAGE TEXT NOT NULL,
  USER_ID INT(11) NOT NULL,
  FOLDER_ID INT(11) NOT NULL,
  IS_READ VARCHAR(50) NOT NULL,
  REQUEST_IS_READ  char(1) NOT NULL,
  USE_SMILES VARCHAR(50) NOT NULL,
  PRIMARY KEY  (ID),
  INDEX IX_B_FORUM_PM_FOLDER(FOLDER_ID),
  INDEX IX_B_FORUM_PM_USER(USER_ID)
);
CREATE TABLE b_forum_pm_folder (
  ID INT(11) NOT NULL AUTO_INCREMENT,
  TITLE VARCHAR(50) NOT NULL,
  USER_ID INT(11) NOT NULL,
  SORT INT(11) NOT NULL,
  PRIMARY KEY  (ID),
  INDEX IX_B_FORUM_PM_FOLDER_USER(USER_ID)
);
CREATE TABLE b_forum_filter (
   ID INT(11) NOT NULL AUTO_INCREMENT,
   DICTIONARY_ID INT(11),
   WORDS VARCHAR(255),
   PATTERN TEXT,
   REPLACEMENT VARCHAR(255),
   DESCRIPTION TEXT,
   USE_IT VARCHAR(50),
   PATTERN_CREATE VARCHAR(5),
   PRIMARY KEY (ID),
   INDEX IX_B_FORUM_FILTER_2(USE_IT),
   INDEX IX_B_FORUM_FILTER_3(PATTERN_CREATE)
);
CREATE TABLE b_forum_dictionary (
   ID INT(11) NOT NULL AUTO_INCREMENT,
   TITLE VARCHAR(50),
   `TYPE` CHAR(1),
   PRIMARY KEY (ID)
);
CREATE TABLE b_forum_letter (
   ID INT(11) NOT NULL AUTO_INCREMENT,
   DICTIONARY_ID INT(11) DEFAULT '0',
   LETTER VARCHAR(50),
   REPLACEMENT VARCHAR(255),
   PRIMARY KEY (ID)
);
CREATE TABLE b_forum_user_forum (
  ID int(11) NOT NULL auto_increment,
  USER_ID int(11),
  FORUM_ID int(11),
  LAST_VISIT datetime,
  MAIN_LAST_VISIT datetime,
  PRIMARY KEY  (ID),
  KEY IX_B_FORUM_USER_FORUM_ID(USER_ID)
);

CREATE TABLE b_forum_user_topic (
   ID bigint(20) not null auto_increment,
   TOPIC_ID INT(11),
   USER_ID INT(11),
   FORUM_ID INT(11),
   LAST_VISIT datetime,
   PRIMARY KEY (TOPIC_ID, USER_ID),
   KEY (ID)
);
CREATE TABLE b_forum_stat (
  ID bigint(20) not null auto_increment,
  USER_ID int(10) default NULL,
  IP_ADDRESS varchar(128) default NULL,
  PHPSESSID varchar(255) default NULL,
  LAST_VISIT datetime default NULL,
  FORUM_ID smallint(5) NOT NULL default '0',
  TOPIC_ID int(10) default NULL,
  SHOW_NAME varchar(101) default NULL,
  PRIMARY KEY(ID),
  KEY IX_B_FORUM_STAT_TOPIC_ID(TOPIC_ID, LAST_VISIT),
  KEY IX_B_FORUM_STAT_FORUM_ID(FORUM_ID, LAST_VISIT),
  KEY IX_B_FORUM_STAT_PHPSESSID(PHPSESSID)
);
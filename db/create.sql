drop table if exists users;
create table users
(
    id bigint(20) unsigned not null auto_increment,
    nickname varchar(100) not null,
    forename varchar(255) not null,
    surname varchar(255) not null,
    email varchar(255) not null,
    password char(32) not null,
    date_of_birth date default null,
    gender char(1),
    country varchar(255),
    description text,
    subscribe int(1) not null default 0,
    activation_code varchar(255),
    activation_date datetime,
    updated_on datetime,
    created_on datetime,
    primary key (id),
    key login_via_email (email,password),
    key login_via_nickname (nickname,password),
    key activation (activation_code),
    key nickname (nickname)
) TYPE=InnoDB;

drop table if exists sessions;
create table sessions
(
    id bigint(20) unsigned not null auto_increment,
	user_id bigint(20) unsigned not null,
    remember_me varchar(32) not null,
    updated_on datetime,
    created_on datetime,
    primary key (id),
    key remember_me (remember_me)
);

drop table if exists articles;
create table articles
(
    id bigint(20) unsigned not null auto_increment,
	user_id bigint(20) unsigned not null,
	title varchar(255) not null,
    normalised_title varchar(255) not null,
	section varchar(255) not null,
	body text,
    url varchar(255),
    image varchar(255),
    audio varchar(255),
    start_date datetime,
    end_date datetime,
    updated_on datetime,
    created_on datetime,
    primary key (id),
	key user (user_id)
) TYPE=InnoDB;

drop table if exists articles_counties;
create table articles_counties (
	article_id bigint(10) unsigned not null,
	county_id int(10) unsigned not null
);

drop table if exists comments;
create table comments
(
    id bigint(20) unsigned not null auto_increment,
	user_id bigint(20) unsigned not null,
	article_id bigint(20) unsigned not null,
	parent_id bigint(20) unsigned,
	title varchar(255) not null,
	body text,
    updated_on datetime,
    created_on datetime,
    primary key (id),
	key author (user_id),
	key article (article_id)
) TYPE=InnoDB;

drop table if exists tags;
create table tags
(
    id bigint(20) unsigned not null auto_increment,
	name varchar(30) not null,
	primary key (id)
) TYPE=InnoDB;

drop table if exists tags_articles;
create table tags_articles
(
    tag_id bigint(20) unsigned not null,
    article_id bigint(20) unsigned not null,
    user_id bigint(20) unsigned not null
) TYPE=InnoDB;

drop table if exists article_ratings;
create table article_ratings
(
    id bigint(20) unsigned not null auto_increment,
	user_id bigint(20) unsigned not null,
	article_id bigint(20) unsigned not null,
	rating int(1) unsigned not null,
	updated_on datetime,
    created_on datetime,
    primary key (id),
	key user (user_id),
	key article (article_id)
);

drop table if exists comment_ratings;
create table comment_ratings
(
    id bigint(20) unsigned not null auto_increment,
	user_id bigint(20) unsigned not null,
	comment_id bigint(20) unsigned not null,
	rating int(1) unsigned not null,
	updated_on datetime,
    created_on datetime,
    primary key (id),
	key user (user_id),
	key comment (comment_id)
);

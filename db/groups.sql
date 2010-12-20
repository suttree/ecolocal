drop table if exists groups;
create table groups (
	id int(10) unsigned not null auto_increment,
	owner_id bigint(10) unsigned not null,
	name varchar(255) not null,
	url_name varchar(255) not null,
	description varchar(255) not null,
	primary key (id),
	key user (owner_id),
	key group_name (name)
);

drop table if exists counties_groups;
create table counties_groups (
	county_id int(10) unsigned not null,
	group_id int(10) unsigned not null
);

drop table if exists groups_users;
create table groups_users (
	user_id bigint(10) unsigned not null,
	group_id int(10) unsigned not null
);




drop table if exists local_events;
create table local_events (
	id int(10) unsigned not null auto_increment,
	article_id int(10) unsigned not null,
	`start_date` datetime default NULL,
	`end_date` datetime default NULL,
	recurrence varchar(255) not null,
	primary key (id)
);

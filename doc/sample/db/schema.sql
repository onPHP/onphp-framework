-- $Id$

-- PostgreSQL:

create sequence message_id;

create table message(
	id bigint default nextval('message_id') primary key,
	nickname varchar(50) not null,
	name varchar(255) not null, -- aka subject, aka title
	content text not null,
	posted timestamp not null
);

-- MySQL:

create table message(
	id bigint not null primary key auto_increment,
	nickname varchar(50) not null,
	name varchar(255) not null, -- aka subject, aka title
	content text not null,
	posted timestamp not null
);

-- Interbase:

create generator message_id;

create table "message" (
	id bigint not null primary key,
	nickname varchar(50) not null,
	name varchar(255) not null, -- aka subject, aka title
	content blob sub_type text,
	posted timestamp not null
);



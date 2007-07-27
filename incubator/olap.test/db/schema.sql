-- $Id$

create table region(
	id integer primary key,
	name varchar(255) not null,
	country varchar(100) null,
	parent_id integer null references region(id)
);

create sequence statistic_visitor_id;
create table statistic_visitor(
	id bigint default nextval('statistic_visitor_id') primary key,
	"when" timestamp not null,
	region_id integer references region(id),
	ip inet
);

create index statistic_visitor_when_idx on statistic_visitor("when");
create index statistic_visitor_region_idx on statistic_visitor(region_id);

create sequence statistic_query_id;
create table statistic_query(
	id bigint default nextval('statistic_query_id') primary key,
	query text not null,
	media varchar(100) not null,
	"when" timestamp not null,
	"found" integer not null,
	region_id integer references region(id),
	ip inet
);

create index statistic_query_query_idx on statistic_query(query);
create index statistic_query_media_idx on statistic_query(media);
create index statistic_query_when_idx on statistic_query("when");
create index statistic_query_region_idx on statistic_query(region_id);

create sequence statistic_click_id;
create table statistic_click(
	id bigint default nextval('statistic_click_id') primary key,
	query_id bigint not null references statistic_query(id),
	"when" timestamp not null,
	site text not null
);

create index statistic_click_query_idx on statistic_click(query_id);
create index statistic_click_site_idx on statistic_click(site);
create index statistic_click_when_idx on statistic_click("when");
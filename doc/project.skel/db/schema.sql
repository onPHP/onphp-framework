-- $Id$

create table administrator(
	id			bigint not null primary key,
	username	varchar(64) not null,
	password	varchar(40) not null -- sha1()
);

-- default admin with 's1kr33t' as password
insert into administrator values(1, 'admin', '10ae3e1317503df06b5f8830853ba5ae3801e4cf');

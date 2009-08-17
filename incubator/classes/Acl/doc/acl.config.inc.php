<?php
/**
 * Acl config example.
 */

Acl::me()->
addRole(
	AclRole::create('foo')->
		grant('news', 'view')->
		grant('news', 'create')->
		grant('news', 'update')->
		grant('news', 'delete')
)->
addRole(
	AclRole::create('bar')->
		grant(
			'pages',
			array(
				'view',
				'create',
				'update',
				'delete',
			)
		)
)->
addRole(
	AclRole::create('baz')->
		inherit('foo')->
		revoke('news', 'delete')
);

?>
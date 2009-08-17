<?php

class AclTest extends TestCase
{
	public function setUp()
	{
		Acl::me()->
		dropAllRoles()->
		addRole(
			AclRole::create('foo')->
				grant('news', array('view', 'add', 'edit', 'delete'))
		)->
		addRole(
			AclRole::create('bar')->
				inherit('foo')->
				grant('pages', array('view', 'add', 'edit', 'delete'))
		);
	}


	/**
	 * @expectedException AclRoleNotExistsException
	 */
	public function testAclRoleNotExistsException()
	{
		Acl::me()->forRole('notExistanceRole');
	}


	public function testAllowedTrue()
	{
		$this->assertTrue(
			Acl::me()->
				forRole('foo')->
				allowed('news', 'view')
		);
		
		$this->assertTrue(
			Acl::me()->
				forRole('foo')->
				allowed('news', 'add')
		);

		$this->assertTrue(
			Acl::me()->
				forRole('foo')->
				allowed('news', 'edit')
		);

		$this->assertTrue(
			Acl::me()->
				forRole('bar')->
				allowed('news', 'add')
		);

		$this->assertTrue(
			Acl::me()->
				forRole('bar')->
				allowed('news', 'edit')
		);

		$this->assertTrue(
			Acl::me()->
				forRole('bar')->
				allowed('pages', 'add')
		);
	}


	public function testDeniedTrue()
	{
		$this->assertTrue(
			Acl::me()->
				forRole('foo')->
				denied('pages', 'view')
		);

		$this->assertTrue(
			Acl::me()->
				forRole('foo')->
				denied('pages', 'add')
		);

		$this->assertTrue(
			Acl::me()->
				forRole('bar')->
				denied('news', 'dosomethingelse')
		);


		Acl::me()->getRole('bar')->revoke('news', 'edit');

		$this->assertTrue(
			Acl::me()->
				forRole('bar')->
				denied('news', 'edit')
		);
		

		$this->assertTrue(
			Acl::me()->
				forRole('bar')->
				denied('news', 'edit')
		);

		
		$this->assertFalse(
			Acl::me()->
				forRole('foo')->
				denied('news', 'edit')
		);
	}


	public function testAllowedFalse()
	{
		$this->assertFalse(
			Acl::me()->
				forRole('foo')->
				allowed('news', 'dosomethingelse')
		);
	}


	public function testDeniedFalse()
	{
		$this->assertFalse(
			Acl::me()->
				forRole('foo')->
				denied('news', 'edit')
		);
	}
}

?>
<?php

final class AclRoleTest extends TestCase
{
	public function setUp()
	{
		Acl::me()->dropAllRoles();
	}


	public function testFixture()
	{
		$role = AclRole::create('foo');
		$this->assertEquals($role->getName(), 'foo');

		$role->setName('bar');
		$this->assertEquals($role->getName(), 'bar');
	}


	public function testGrant()
	{
		$role = AclRole::create('foo')->
			grant('news', 'view')->
			grant('news', 'add')->
			grant('news', 'edit');


		$this->assertEquals(
			array(
				'news' => array(
					'view'  => true,
					'add'   => true,
					'edit'  => true,
				)
			),
			$role->getList()
		);
	}


	public function testRevoke()
	{
		$role = AclRole::create('foo')->
			grant('news', 'view')->
			grant('news', 'edit');

		$role->revoke('news', 'edit');


		$this->assertEquals(
			array(
				'news' => array(
					'view' => true
				)
			),
			$role->getList()
		);
	}


	public function testInherit()
	{
		Acl::me()->
		addRole(
			AclRole::create('foo')->
				grant('news', 'view')->
				grant('news', 'add')->
				grant('news', 'edit')->
				grant('news', 'delete')->
				grant('users', 'view')->
				grant('users', 'edit')->
				grant('users', 'delete')
		)->
		addRole(
			AclRole::create('bar')->
				inherit('foo')
		);


		$bar = Acl::me()->
			getRole('bar');

		
		$this->assertEquals(
			array(
				'news'  => array(
					'view'    =>  true,
					'add'     =>  true,
					'edit'    =>  true,
					'delete'  =>  true,
				),
				'users' => array(
					'view'    =>  true,
					'edit'    =>  true,
					'delete'  =>  true,
				),
			),
			$bar->getList()
		);
	}


	public function testGrantManyArray()
	{
		$role = AclRole::create('foo')->
			grant('news', array('view', 'add', 'edit', 'delete'));


		$this->assertEquals(
			array(
				'news' => array(
					'view'     =>   true,
					'add'      =>   true,
					'edit'     =>   true,
					'delete'   =>   true,
				),
			),
			$role->getList()
		);
	}


	public function testRevokeManyArray()
	{
		$role = AclRole::create('foo')->
			grant('news', array('view', 'add', 'edit', 'delete'))->
			revoke('news', array('edit', 'delete'));


		$this->assertEquals(
			array(
				'news' => array(
					'view'     =>   true,
					'add'      =>   true,
				),
			),
			$role->getList()
		);
	}
}

?>
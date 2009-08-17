<?php

class AclMappedControllerTest extends TestCase
{
	private $controller  = null;


	public function setUp()
	{
		Acl::me()->
		dropAllRoles()->
		addRole(
			AclRole::create('foo')->
				grant('AclMappedController', 'view')
		);


		$this->controller = new AclMappedController('foo');
		$this->controller->setMethodMappingList(
			array(
				// action  => methodName
				'view'     => 'view',
				'add'      => 'add',
				'edit'     => 'edit',
				'delete'   => 'delete',
			)
		);
	}


	public function testRoleSetting()
	{
		$this->assertEquals('foo', $this->controller->getRole());
	}


	public function testGetAllowedAction()
	{
		$request = HttpRequest::create()->
			setGet(
				array(
					'action'      =>  'view'
				)
			);
		

		$this->assertEquals('view', $this->controller->chooseAction($request));
	}


	public function testPostAllowedAction()
	{
		$request = HttpRequest::create()->
			setPost(
				array(
					'action'      =>  'view'
				)
			);


		$this->assertEquals('view', $this->controller->chooseAction($request));
	}


	public function testAttachedAllowedAction()
	{
		$request = HttpRequest::create()->
			setAttachedVar('action', 'view');

		$this->assertEquals('view', $this->controller->chooseAction($request));
	}


	/**
	 * @expectedException AclDeniedException
	 */
	public function testGetDeniedAction()
	{
		$request = HttpRequest::create()->
			setGet(
				array('action' => 'edit')
			);

		$this->controller->chooseAction($request);
	}


	/**
	 * @expectedException AclDeniedException
	 */
	public function testPostDeniedAction()
	{
		$request = HttpRequest::create()->
			setPost(
				array('action' => 'edit')
			);

		$this->controller->chooseAction($request);
	}


	/**
	 * @expectedException AclDeniedException
	 */
	public function testAttachedDeniedAction()
	{
		$request = HttpRequest::create()->
			setAttachedVar('action', 'edit');

		$this->controller->chooseAction($request);
	}
}

?>
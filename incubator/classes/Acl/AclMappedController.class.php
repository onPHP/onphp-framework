<?php

/**
 * AclMappedController
 *
 * @ingroup Acl
 * 
 * @author Petr 'PETRUHA' Korobeinikov <onphp at petruha.net>
 */
final class AclMappedController
	extends MethodMappedController
{
	/**
	 * 
	 *
	 * @var string
	 */
	private $roleName = null;


	public function create($roleName)
	{
		return new self($roleName);
	}


	public function  __construct($roleName)
	{
		$this->roleName = $roleName;
	}


	/**
	 *
	 * @param HttpRequest $request
	 * @return <type>
	 */
	public function chooseAction(HttpRequest $request)
	{
		$action = parent::chooseAction($request);

		if (Acl::me()->forRole($this->roleName)->allowed(__CLASS__, $action)) {
			return $action;
		}

		throw new AclDeniedException("Access to '$action' on this controller denied for role '{$this->roleName}'");
	}


	/**
	 * Returns role for check permissions
	 *
	 * @return string
	 */
	public function getRole()
	{
		return $this->roleName;
	}


	/**
	 *
	 * @param string $roleName
	 * @return AclMappedController
	 */
	public function setRole($roleName)
	{
		$this->roleName = $roleName;

		return $this;
	}
}

?>
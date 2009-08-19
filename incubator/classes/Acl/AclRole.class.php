<?php

/**
 * AclRole
 *
 * @ingroup Acl
 *
 * @see http://framework.zend.com/manual/en/zend.acl.html
 * @author Petr 'PETRUHA' Korobeinikov <onphp at petruha.net>
 */
final class AclRole
{
	const GRANT  = true;
	const REVOKE = false;
	

	/**
	 * The name of the role. Must be unique.
	 *
	 * @var string
	 */
	private $name = null;


	/**
	 * Permissions of the role.
	 *
	 * @var array
	 */
	private $permissionsList = array();


	/**
	 * The name of the role must be unique.
	 *
	 * @param string $name
	 */
	public static function create($name = null)
	{
		return new self($name);
	}


	public function  __construct($name = null)
	{
		$this->name = $name;
	}
	

	/**
	 * Grants permission to action on controller for the role.
	 *
	 * @param string $controller
	 * @param array $actions
	 */
    public function grant($controller, $actions)
	{
		$this->alterPermission($controller, $actions, self::GRANT);

		return $this;
	}


	/**
	 * Revokes permission to action on controller for the role.
	 *
	 * @param string $controller
	 * @param array $actions
	 */
	public function revoke($controller, $actions)
	{
		$this->alterPermission($controller, $actions, self::REVOKE);

		return $this;
	}


	/**
	 * Inherits permissions from role.
	 * You can inherit from many roles.
	 *
	 * @param string $from
	 */
	public function inherit($from)
	{
		$this->permissionsList = array_merge(
			$this->permissionsList,
			Acl::me()->
				getRole($from)->
				getList()
		);

		return $this;
	}


	/**
	 * Returns list of permissions for the role.
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->permissionsList;
	}


	/**
	 * Gets name of the role.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Sets name of the role.
	 *
	 * @param string $name
	 * @return AclRole
	 */
	public function setName(/* string */ $name)
	{
		$this->name = $name;

		return $this;
	}


	/**
	 * Grants or revokes permissions for role on controller.
	 *
	 * @param string $controller
	 * @param boolean $grant
	 * @param array $actions
	 */
	private function alterPermission(
		/* string  */   $controller,
		/* array   */   $actions,
		/* boolean */   $grant
	)
	{
		Assert::isString($controller);
		Assert::isBoolean($grant);

		if (is_string($actions)) {
			$actions = array($actions);
		}

		foreach ($actions as $action) {
			if ($grant) {
				$this->permissionsList[$controller][$action] = true;
			} else {
				unset($this->permissionsList[$controller][$action]);
			}
		}
	}
}

?>
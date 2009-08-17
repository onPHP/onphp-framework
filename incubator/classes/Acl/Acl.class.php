<?php

/**
 * Acl
 *
 * @todo Storing acl in memory.
 *
 * @ingroup Acl
 * 
 * @see http://framework.zend.com/manual/en/zend.acl.html
 * @author Petr 'PETRUHA' Korobeinikov <onphp at petruha.net>
 */
final class Acl
	extends Singleton
{
	/**
	 * The role for check permission in $this->forRole()
	 *
	 * @var AclRole
	 */
	private $currentRole = null;


	/**
	 * Storage of roles. May be use Scope?
	 *
	 * @var array
	 */
	private $rolesList = array();
	

	/**
	 * Returns the instance of self.
	 *
	 * @return Acl
	 */
	public static function me()
	{
		return self::getInstance(__CLASS__);
	}


	/**
	 * Appends new role to the access control list.
	 * 
	 * @throws AclRoleExistsException
	 * @param AclRole $role
	 * @return Acl
	 */
	public function addRole(AclRole $role)
	{
		$roleName = $role->getName();

		if (array_key_exists($roleName, $this->rolesList)) {
			throw new AclRoleExistsException("Role named '$roleName' already exists.");
		}
		
		$this->rolesList[$roleName] = $role;

		return $this;
	}


	/**
	 * Returns role named $roleName.
	 *
	 * @param string $roleName
	 * @return AclRole
	 */
	public function getRole(/* string */ $roleName)
	{
		if (array_key_exists($roleName, $this->rolesList)) {
			return $this->rolesList[$roleName];
		}

		throw new AclRoleNotExistsException("Role named '$roleName' is not exists.");
	}


	/**
	 * Drops all roles from Acl.
	 * 
	 * @todo Check memory usage after calling array().
	 */
	public function dropAllRoles()
	{
		$this->rolesList = array();

		return $this;
	}


	/**
	 * Returns list of roles.
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->rolesList;
	}


	/**
	 * Sets the current role for check.
	 *
	 * @param string $roleName
	 * @return Acl
	 */
	public function forRole(/* string */ $roleName)
	{
		if (array_key_exists($roleName, $this->rolesList)) {
			$this->currentRole = $roleName;
			
			return $this;
		}

		throw new AclRoleNotExistsException("Role named '$roleName' is not exists.");
	}


	/**
	 * Returns true if action allowed for role.
	 *
	 * @param string $roleName
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	public function /* boolean */ allowed(/* string */ $controller, /* string */ $action)
	{
		$list = self::me()->
			getRole($this->currentRole)->
			getList();

		return isset($list[$controller][$action]);
	}


	/**
	 * Returns true if action denied for role.
	 *
	 * @param string $roleName
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	public function /* boolean */ denied(/* string */ $controller, /* string */ $action)
	{
		return !$this->allowed($controller, $action);
	}
}

?>
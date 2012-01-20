<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 27.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * NoSQL-connector's implementation basis.
 *
 * @ingroup NoSQL
**/
abstract class NoSQL {

	// credentials
	protected $username	= null;
	protected $password	= null;
	protected $hostname	= null;
	protected $port		= null;

	// queries
	abstract public function select();
	abstract public function insert();
	abstract public function update();
	abstract public function delete();

	// full table queries
	abstract public function getAllObjects();
	abstract public function getTotalCount();

	// custom queries
	abstract public function getCustomList();
	abstract public function getCustomData();

	/**
	 * Shortcut
	 * @static
	 * @param string $connector
	 * @param string $user
	 * @param string $pass
	 * @param string $host
	 * @param string $port
	 * @return NoSQL
	 */
	public static function spawn( $connector, $user, $pass, $host, $port=null ) {
		$db = new $connector;

		$db->
			setUsername($user)->
			setPassword($pass)->
			setHostname($host);
		if( !empty($port) ) {
			$db->setPort($port);
		}

		return $db;
	}

	/**
	 * @param string $name
	 * @return NoSQL
	 */
	public function setUsername($name) {
		$this->username = $name;

		return $this;
	}

	/**
	 * @param string $password
	 * @return NoSQL
	 */
	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}

	/**
	 * @param string $host
	 * @return NoSQL
	 */
	public function setHostname($host) {
		$port = null;

		if (strpos($host, ':') !== false)
			list($host, $port) = explode(':', $host, 2);

		$this->hostname = $host;
		$this->port = $port;

		return $this;
	}

	public function setPort($port) {
		$this->port = $port;

		return $this;
	}

}

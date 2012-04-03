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
 * Pool of NoSQL's instances.
 *
 * @ingroup NoSQL
**/
final class NoSqlPool extends Singleton implements Instantiatable
{
	private $default = null;

	private $pool = array();

	/**
	 * @return NoSqlPool
	**/
	public static function me()
	{
		return Singleton::getInstance(__CLASS__);
	}

	/**
	 * @static
	 * @param GenericDAO $dao
	 * @return NoSQL
	 */
	public static function getByDao(GenericDAO $dao)
	{
		return self::me()->getLink($dao->getLinkName());
	}

	/**
	 * @param NoSQL $db
	 * @return NoSqlPool
	 */
	public function setDefault(NoSQL $db)
	{
		$this->default = $db;

		return $this;
	}

	/**
	 * @return NoSqlPool
	**/
	public function dropDefault()
	{
		$this->default = null;

		return $this;
	}

	/**
	 * @param string $name
	 * @param NoSQL $db
	 * @return NoSqlPool
	 * @throws WrongArgumentException
	 */
	public function addLink($name, NoSQL $db)
	{
		if (isset($this->pool[$name]))
			throw new WrongArgumentException(
				"already have '{$name}' link"
			);

		$this->pool[$name] = $db;

		return $this;
	}

	/**
	 * @param string $name
	 * @return NoSqlPool
	 * @throws MissingElementException
	 */
	public function dropLink($name)
	{
		if (!isset($this->pool[$name]))
			throw new MissingElementException(
				"link '{$name}' not found"
			);

		unset($this->pool[$name]);

		return $this;
	}

	/**
	 * @param string $name
	 * @return NoSQL
	 * @throws MissingElementException
	 */
	public function getLink($name = null)
	{
		/** @var $link NoSQL */
		$link = null;

		// single-NoSQL project
		if (!$name) {
			if (!$this->default) {
				throw new MissingElementException(
					'i have no default link and requested link name is null'
				);
			}

			$link = $this->default;
		} elseif (isset($this->pool[$name])) {
			$link = $this->pool[$name];
		}
		// check if found and return
		if ($link) {
			if (!$link->isConnected())
				$link->connect();

			return $link;
		}

		throw new MissingElementException(
			"can't find link with '{$name}' name"
		);
	}

	/**
	 * @return NoSqlPool
	 */
	public function shutdown()
	{
		$this->default = null;
		$this->pool = array();

		return $this;
	}
}
?>
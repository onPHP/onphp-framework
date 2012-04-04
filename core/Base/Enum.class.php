<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Parent of all enumeration classes.
	 *
	 * @see MimeType for example
	 *
	 * @ingroup Base
	 * @ingroup Module
	**/
	abstract class Enum extends NamedObject
		implements
			Serializable
	{
		protected static $names = array(/* override me */);

		/**
		 * @param integer $id
		 * @return Enum
		 */
		public static function create($id)
		{
			$className = get_called_class();
			return new $className($id);
		}

		public function __construct($id)
		{
			$this->setInternalId($id);
		}

		/**
		 * @param $id
		 * @return Enum
		 * @throws MissingElementException
		 */
		protected function setInternalId($id)
		{
			$names = static::$names;

			if (isset($names[$id])) {
				$this->id = $id;
				$this->name = $names[$id];
			} else
				throw new MissingElementException(
					'knows nothing about such id == '.$id
				);

			return $this;
		}

		/**
		 * @return string
		 */
		public function serialize()
		{
			return (string) $this->id;
		}

		/**
		 * @param $serialized
		 */
		public function unserialize($serialized)
		{
			$this->setInternalId($serialized);
		}

		/**
		 * Array of object
		 * @static
		 * @return array
		 */
		public static function getList()
		{
			$list = array();
			foreach (array_keys(static::$names) as $id)
				$list[] = static::create($id);

			return $list;
		}

		/**
		 * must return any existent ID
		 * 1 should be ok for most enumerations
		 * @return integer
		**/
		public static function getAnyId()
		{
			return 1;
		}

		/**
		 * @return null|integer
		 */
		public function getId()
		{
			return $this->id;
		}


		/**
		 * Alias for getList()
		 * @static
		 * @deprecated
		 * @return array
		 */
		public static function getObjectList()
		{
			return static::getList();
		}

		/**
		 * @return string
		 */
		public function toString()
		{
			return $this->name;
		}

		/**
		 * Plain list
		 * @static
		 * @return array
		 */
		public static function getNameList()
		{
			return static::$names;
		}

		/**
		 * @return Enum
		**/
		public function setId($id)
		{
			throw new UnsupportedMethodException('You can not change id here, because it is politics for Enum!');
		}
	}
?>
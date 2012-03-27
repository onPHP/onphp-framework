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
	 * @see AccessMode for example
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

		final public function __construct($id)
		{
			$this->setId($id);
		}

		/// prevent's serialization of names' array
		//@{
		public function serialize()
		{
			return (string) $this->id;
		}

		public function unserialize($serialized)
		{
			$this->setId($serialized);
		}
		//@}

		public static function getList()
		{
			return static::$names;
		}

		/**
		 * must return any existent ID
		 * 1 should be ok for most enumerations
		**/
		public static function getAnyId()
		{
			return 1;
		}

		/// parent's getId() is too complex in our case
		public function getId()
		{
			return $this->id;
		}

		public static function getObjectList()
		{
			$list = array();
			$names = static::$names;

			foreach (array_keys($names) as $id)
				$list[] = static::create($id);

			return $list;
		}

		public function toString()
		{
			return $this->name;
		}

		public static function getNameList()
		{
			return static::$names;
		}

		/**
		 * @return Enum
		**/
		public function setId($id)
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
	}
?>
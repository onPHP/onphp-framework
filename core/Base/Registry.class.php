<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey V. Gorbylev                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Parent of all registry classes.
	 *
	 * @see MimeType for example
	 *
	 * @ingroup Base
	 * @ingroup Module
	**/
	abstract class Registry extends NamedObject
		implements
			Serializable
	{

		const NIL = 'nil';

		protected static $names = array(
			self::NIL => 'Unknown'
		);

		/**
		 * @param integer $id
		 * @return Registry
		 */
		public static function create($id)
		{
			return new static($id);
		}

		public function __construct($id)
		{
			$this->setInternalId($id);
		}

		/**
		 * @param $id
		 * @return Registry
		 * @throws MissingElementException
		 */
		protected function setInternalId($id)
		{
			if (isset(static::$names[$id])) {
				$this->id = $id;
				$this->name = static::$names[$id];
			} else
				throw new MissingElementException(
					get_class($this) . ' knows nothing about such id == '.$id
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
			return static::NIL;
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
			throw new UnsupportedMethodException('You can not change id here, because it is politics for Registry!');
		}
	}
?>
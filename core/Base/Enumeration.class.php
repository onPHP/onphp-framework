<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
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
	abstract class Enumeration extends NamedObject implements Serializable
	{
		protected $names = array(/* override me */);

		public function __construct($id)
		{
			$this->setId($id);
		}

		/// useful helper methods
		//@{
		/**
		 * @param $id
		 * @return static
		 */
		public static function create($id)
		{
			return new static($id);
		}

		/**
		 * @param int|static $enum
		 * @throws WrongArgumentException
		 * @return boolean
		 */
		public function is($enum)
		{
			if (is_scalar($enum)) {
				$id = $enum;
			} else if (is_object($enum) && is_a($enum, get_class($this))) {
				$id = $enum->getId();
			} else {
				throw new WrongArgumentException('cant match this enum with: ' . var_export($enum, true));
			}
			return $id == $this->getId();
		}

		/**
		 * @param int[]|static[] $enums
		 * @return bool
		 * @throws WrongArgumentException
		 */
		public function in(array $enums)
		{
			foreach ($enums as $enum) {
				if ($this->is($enum)) {
					return true;
				}
			}
			return false;
		}
		//@}

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

		public static function getList(Enumeration $enum)
		{
			return $enum->getObjectList();
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

		public function getObjectList()
		{
			$list = array();
			$names = $this->getNameList();

			foreach (array_keys($names) as $id)
				$list[$id] = new $this($id);

			return $list;
		}

		public function toString()
		{
			return $this->name;
		}

		public function getNameList()
		{
			return $this->names;
		}

		/**
		 * @return Enumeration
		**/
		public function setId($id)
		{
			$names = $this->getNameList();

			if (isset($names[$id])) {
				$this->id = $id;
				$this->name = $names[$id];
			} else
				throw new MissingElementException(
					get_class($this) . ' knows nothing about such id == '.$id
				);

			return $this;
		}

		/**
		 * @static
		 * @return static[]
		 */
		public static function makeObjectList() {
			$enum = new static( static::getAnyId() );
			return $enum->getObjectList();
		}

		/**
		 * @static
		 * @return array
		 */
		public static function makeNameList() {
			$enum = new static( static::getAnyId() );
			return $enum->getNameList();
		}
	}
?>
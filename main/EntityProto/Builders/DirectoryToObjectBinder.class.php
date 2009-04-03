<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class DirectoryToObjectBinder extends ObjectBuilder
	{
		private $identityMap = array();

		/**
		 * @return FormToObjectConverter
		**/
		public static function create(EntityProto $proto)
		{
			return new self($proto);
		}
		
		public function setIdentityMap(&$identityMap)
		{
			$this->identityMap = &$identityMap;

			return $this;
		}

		public function getIdentityMap()
		{
			return $this->identityMap;
		}

		/**
		 * @return PrototypedBuilder
		**/
		public function cloneBuilder(EntityProto $proto)
		{
			$result = parent::cloneBuilder($proto);

			$result->setIdentityMap($this->identityMap);

			return $result;
		}

		public function cloneInnerBuilder($property)
		{
			$result = parent::cloneInnerBuilder($property);

			$result->setIdentityMap($this->identityMap);

			return $result;
		}

		/**
		 * @return PrototypedBuilder
		**/
		public function makeReverseBuilder()
		{
			$reverseIdentityMap = array();

			foreach ($this->identityMap as $dir => $object) {
				$reverseIdentityMap[spl_object_hash($object)] = $dir;
			}

			return
				ObjectToDirectoryBinder::create($this->proto)->
				setIdentityMap($reverseIdentityMap);
		}

		public function make($object, $recursive = true)
		{
			$realObject = $object;

			if (is_link($object)) {
				$realObject = readlink($object);

				if ($realObject === false)
					throw new WrongStateException('invalid pointer: '.$object);
			}

			if (array_key_exists($realObject, $this->identityMap)) {
				$result = $this->identityMap[$realObject];

				return $result;
			}

			$result = parent::make($object, $recursive);

			return $result;
		}

		protected function initialize($object, &$result)
		{
			parent::initialize($object, $result);

			$realObject = $object;

			if (is_link($object)) {
				$realObject = readlink($object);
			}

			$this->identityMap[$realObject] = $result;

			return $this;
		}

		/**
		 * @return FormGetter
		**/
		protected function getGetter($object)
		{
			return new DirectoryGetter($this->proto, $object);
		}
		
		/**
		 * @return ObjectSetter
		**/
		protected function getSetter(&$object)
		{
			return new ObjectSetter($this->proto, $object);
		}
	}
?>
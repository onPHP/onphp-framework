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

	abstract class DirectoryBuilder extends PrototypedBuilder
	{
		protected $directory	= null;
		protected $permissions	= 0700;
		protected $identityMap	= null;

		public function __construct(EntityProto $proto)
		{
			parent::__construct($proto);

			$this->identityMap = new DirectoryContext;
		}

		public function setDirectory($directory)
		{
			$this->directory = $directory;
			
			return $this;
		}
		
		public function getDirectory()
		{
			return $this->directory;
		}

		public function setPermissions($permissions)
		{
			$this->permissions = $permissions;

			return $this;
		}

		public function getPermissions()
		{
			return $this->permissions;
		}

		public function setIdentityMap(DirectoryContext $identityMap)
		{
			$this->identityMap = $identityMap;

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

			$result->
				setDirectory($this->directory)->
				setPermissions($this->permissions)->
				setIdentityMap($this->identityMap);

			return $result;
		}

		public function cloneInnerBuilder($property)
		{
			$this->checkDirectory();

			$result = parent::cloneInnerBuilder($property);

			$result->
				setDirectory($this->directory.'/'.$property)->
				setPermissions($this->permissions)->
				setIdentityMap($this->identityMap);

			return $result;
		}

		public function makeListItemBuilder($object)
		{
			$this->checkDirectory();

			if (!$object instanceof Identifiable)
				throw new WrongArgumentException(
					'cannot build list of items without identity'
				);

			return $this->cloneBuilder($this->proto)->
				setPermissions($this->permissions)->
				setDirectory($this->directory.'/'.$object->getId());
		}

		protected function createEmpty()
		{
			$result = $this->directory;

			if (!file_exists($result))
				mkdir($result, $this->permissions, true);
			elseif (is_link($result)) {
				throw new WrongStateException(
					'cannot make object by reference: '.$this->directory
				);
			}

			return $result;
		}

		protected function safeClean()
		{
			if (file_exists($this->directory) || is_link($this->directory)) {
				if (!is_link($this->directory)) {
					throw new WrongStateException(
						'you should remove the storage '
						.$this->directory
						.' by your hands'
					);
				}

				unlink($this->directory);
			}

			return $this;
		}

		protected function checkDirectory()
		{
			if (!$this->directory)
				throw new WrongStateException(
					'you must specify the context for this builder'
				);

			return $this;
		}
	}

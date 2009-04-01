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

	final class ObjectToDirectoryBinder extends DirectoryBuilder
	{
		/**
		 * @return ObjectToFormConverter
		**/
		public static function create(EntityProto $proto)
		{
			return new self($proto);
		}
		
		public function make($object, $recursive = true)
		{
			if (!$this->directory)
				throw new WrongStateException(
					'you must specify the context for this builder'
				);

			if (!$object) {
				$this->safeClean();

				return $this->directory;
			}

			$id = spl_object_hash($object);

			$realDirectory = null;

			if (is_link($this->directory)) {
				$realDirectory = readlink($this->directory);

				if ($realDirectory === false)
					throw new WrongStateException(
						'invalid pointer: '.$this->directory
					);
			}

			if (
				!isset($this->identityMap[$id])
				&& is_link($this->directory)
			) {
				throw new WrongStateException(
					'you must always store your object somewhere '
					.'before you going to update pointer '
					.$this->directory
				);
			}

			if (
				isset($this->identityMap[$id])
				&& file_exists($this->directory)
				&& !$realDirectory
				&& $this->directory != $this->identityMap[$id]
			) {
				throw new WrongStateException(
					'you should relocate object '
					.$this->directory.' to '
					.$this->identityMap[$id]
					.' by yourself.'
					.' cannot replace object with a link'
				);
			}

			if (
				isset($this->identityMap[$id])
				&& (
					!file_exists($this->directory)
					|| $realDirectory
				)
			) {
				if (
					!$realDirectory
					|| $realDirectory != $this->identityMap[$id]
				) {
					$this->safeClean();

					$status = symlink($this->identityMap[$id], $this->directory);

					if ($status !== true)
						throw new WrongStateException(
							'error creating symlink'
						);
				}

				return $this->identityMap[$id];
			}

			$result = parent::make($object, $recursive);

			$this->identityMap[$id] = $result;

			return $result;
		}

		/**
		 * @return PrototypedBuilder
		**/
		public function makeReverseBuilder()
		{
			return
				DirectoryToObjectBinder::create($this->proto)->
				setIdentityMap($this->identityMap);
		}

		/**
		 * @return ObjectGetter
		**/
		protected function getGetter($object)
		{
			return new ObjectGetter($this->proto, $object);
		}
		
		/**
		 * @return FormSetter
		**/
		protected function getSetter(&$object)
		{
			return new DirectorySetter($this->proto, $object);
		}
	}
?>
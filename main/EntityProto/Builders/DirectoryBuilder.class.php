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

	abstract class DirectoryBuilder extends PrototypedBuilder
	{
		protected $directory = null;

		public function setDirectory($directory)
		{
			$this->directory = $directory;
			
			return $this;
		}
		
		public function getDirectory()
		{
			return $this->directory;
		}

		/**
		 * @return PrototypedBuilder
		**/
		public function cloneBuilder(EntityProto $proto)
		{
			$result = parent::cloneBuilder($proto);

			$result->setDirectory($this->directory);

			return $result;
		}

		public function cloneInnerBuilder($property)
		{
			$result = parent::cloneInnerBuilder($property);

			$result->setDirectory($this->directory.'/'.$property);

			return $result;
		}

		protected function createEmpty()
		{
			if (!$this->directory)
				throw new WrongStateException(
					'you must specify the context for this builder'
				);

			if (!file_exists($this->directory))
				mkdir($this->directory, 0700, true);

			return $this->directory;
		}
	}
?>
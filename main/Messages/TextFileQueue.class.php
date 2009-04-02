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

	class TextFileQueue implements MessageQueue
	{
		private $fileName	= null;
		private $offset		= null;

		public static function create()
		{
			return new self;
		}

		public function setFileName($fileName)
		{
			$this->fileName = $fileName;

			return $this;
		}

		public function getFileName()
		{
			return $this->fileName;
		}

		public function setOffset($offset)
		{
			$this->offset = $offset;

			return $this;
		}

		public function getOffset()
		{
			return $this->offset;
		}
	}
?>

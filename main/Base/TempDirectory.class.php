<?php
/***************************************************************************
 *   Copyright (C) 2006 by Ivan Khvostishkov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class TempDirectory
	{
		private $path		= null;

		protected $prefix	= 'TmpDir';

		public function __construct($directory = 'temp-garbage/')
		{
			$this->path =
				FileUtils::makeTempDirectory($directory, $this->prefix);
		}

		public function __destruct()
		{
			try {
				FileUtils::removeDirectory($this->path, true);
			} catch (Exception $e) {
				// boo! deal with garbage yourself.
			}
		}

		public function getPath()
		{
			return $this->path;
		}
	}
?>
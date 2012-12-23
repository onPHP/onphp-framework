<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	/**
	 * NamespaceDirScanerOnPHP class to scan directories and save which class where
	 */
	class NamespaceDirScanerOnPHP extends NamespaceDirScaner
	{
		public function scan($directory, $namespace = '')
		{
			$this->list[$this->dirCount] = $directory;

			if ($paths = glob($directory.'*'.$this->classExtension, GLOB_NOSORT)) {
				foreach ($paths as $path) {
					$fullClassName = ($namespace ? ('\\' . $namespace) : '') . '\\'
						.basename($path, $this->classExtension);
					if (!isset($this->list[$fullClassName]))
						$this->list[$fullClassName] = $this->dirCount;
				}
			}

			++$this->dirCount;
		}
	}

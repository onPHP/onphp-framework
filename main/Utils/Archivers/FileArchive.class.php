<?php
/***************************************************************************
 *   Copyright (C) 2006 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class FileArchive
	{
		protected $cmdBinPath	= null;
		protected $sourceFile	= null;

		abstract public function readFile($fileName);

		public function __construct($cmdBinPath = null)
		{
			if (!is_executable($cmdBinPath))
				throw
					new WrongStateException(
						"cannot find executable {$cmdBinPath}"
					);

			$this->cmdBinPath = $cmdBinPath;
		}

		public function open($sourceFile)
		{
			if (!is_readable($sourceFile))
				throw
					new WrongStateException(
						"cannot open file {$sourceFile}"
					);
			
			$this->sourceFile = $sourceFile;

			return $this;
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * GNU Tar wrapper.
	 * 
	 * @see http://www.gnu.org/software/tar/
	 *
	 * @ingroup Utils
	**/
	final class TarArchive extends FileArchive
	{
		public function __construct($cmdBinPath = '/bin/tar')
		{
			if ($cmdBinPath === null)
				throw
					new UnimplementedFeatureException(
						'no built-in support for GNU Tar'
					);

			parent::__construct($cmdBinPath);
		}

		public function readFile($fileName)
		{
			if (!$this->sourceFile)
				throw
					new WrongStateException(
						'dude, open an archive first.'
					);
			
			$options = '--extract --to-stdout'
				.' --file '.escapeshellarg($this->sourceFile)
				.' '.escapeshellarg($fileName);

			return $this->execStdoutOptions($options);
		}
	}
?>
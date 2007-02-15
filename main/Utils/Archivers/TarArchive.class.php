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

	/**
	 * GNU tar wrapper
	**/
	final class TarArchive extends FileArchive
	{
		public function __construct($cmdBinPath = '/usr/bin/tar')
		{
			parent::__construct($cmdBinPath);
		}

		public function readFile($fileName)
		{
			if (!$this->sourceFile)
				throw
					new WrongStateException(
						'dude, open an archive first.'
					);
			
			return
				$this->execOptionsWithFileList(
					'--extract --to-stdout'.$this->makeOptionString(),
					array($fileName)
				);
		}

		private function makeOptionString()
		{
			$result = null;

			if ($this->sourceFile)
				$result .= ' --file '.escapeshellarg($this->sourceFile);
			
			return $result;
		}

		private function execOptionsWithFileList($options, $archiveFilesList = array())
		{
			$cmd = escapeshellcmd($this->cmdBinPath.' '.$options);

			foreach ($archiveFilesList as $archiveFile)
			{
				$cmd .= ' '.escapeshellarg($archiveFile);
			}

			ob_start();
			
			passthru($cmd.' 2>/dev/null', $exitStatus);
			
			$output = ob_get_clean();

			if ($exitStatus != 0)
				throw
					new ArchiverException(
						$this->cmdBinPath." failed with error code = {$exitStatus}"
					);

			return $output;
		}
	}
?>
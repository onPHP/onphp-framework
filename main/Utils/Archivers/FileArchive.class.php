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
	 * @ingroup Utils
	**/
	abstract class FileArchive
	{
		protected $cmdBinPath	= null;
		protected $sourceFile	= null;

		abstract public function readFile($fileName);

		public function __construct($cmdBinPath = null)
		{
			if ($cmdBinPath !== null) {
				if (!is_executable($cmdBinPath))
					throw new WrongStateException(
						'cannot find executable '.$cmdBinPath
					);

				$this->cmdBinPath = $cmdBinPath;
			}
		}
		
		/**
		 * @return FileArchive
		**/
		public function open($sourceFile)
		{
			if (!is_readable($sourceFile))
				throw new WrongStateException(
					'cannot open file '.$sourceFile
				);
			
			$this->sourceFile = $sourceFile;

			return $this;
		}

		protected function execStdoutOptions($options)
		{
			if (!$this->cmdBinPath)
				throw new WrongStateException(
					'nothing to exec'
				);

			$cmd = escapeshellcmd($this->cmdBinPath.' '.$options);

			ob_start();
			
			$exitStatus = null;
			
			passthru($cmd.' 2>/dev/null', $exitStatus);
			
			$output = ob_get_clean();

			if ($exitStatus != 0)
				throw new ArchiverException(
					$this->cmdBinPath.' failed with error code = '.$exitStatus
				);

			return $output;
		}
	}
?>
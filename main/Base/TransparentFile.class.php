<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class TransparentFile
	{
		private $path		= null;
		private $rawData	= null;

		private $tempFile	= null;

		public static function create()
		{
			return new self;
		}

		public function setPath($path)
		{
			if (!is_readable($path))
				throw new WrongArgumentException(
					"cannot open source file {$path}"
				);

			$this->path = $path;

			$this->tempFile = null;
			$this->rawData = null;

			return $this;
		}

		public function getPath()
		{
			if (!$this->path && $this->rawData) {
				$this->tempFile = new TempFile();

				$this->path = $this->tempFile->getPath();

				file_put_contents($this->path, $this->rawData);
			}

			return $this->path;
		}

		public function setRawData($rawData)
		{
			$this->rawData = $rawData;
			
			$this->tempFile = null;
			$this->path = null;
			
			return $this;
		}

		public function getRawData()
		{
			if (!$this->rawData && $this->path) {
				$this->rawData = file_get_contents($this->path);
			}

			return $this->rawData;
		}
	}
?>
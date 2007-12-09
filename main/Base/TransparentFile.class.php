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
/* $Id$ */

	final class TransparentFile
	{
		private $path		= null;
		private $rawData	= null;
		
		private $tempFile	= null;
		
		/**
		 * @return TransparentFile
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return TransparentFile
		**/
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
		
		/**
		 * @return TransparentFile
		**/
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
		
		public function getSize()
		{
			if ($this->rawData)
				return strlen($this->rawData);
			elseif ($this->path)
				return filesize($this->path);
			
			return null;
		}
	}
?>
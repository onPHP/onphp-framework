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
	 * Abstract class to scan directories,
	 *    see NamespaceDirScanerPSR0 and NamespaceDirScanerOnPHP
	 */
	abstract class NamespaceDirScaner
	{
		protected $classExtension = EXT_CLASS;
		protected $list = array();
		protected $dirCount = 0;
		
		public function __construct() {
			;
		}
		
		abstract public function scan($directory, $namespace = '');
		
		/**
		 * @param string $classExtension
		 * @return NamespaceDirScaner
		 */
		public function setClassExtension($classExtension)
		{
			$this->classExtension = $classExtension;
			return $this;
		}
		
		public function clear() {
			$this->list = array();
			$this->dirCount = 0;
		}
		
		public function getList() {
			return $this->list;
		}
	}

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
	
	class HostConfiguration extends Singleton implements Instantiatable
	{
		const TEST				= 'test';
		const PRODUCTION		= 'production';
		const LOCAL				= 'localhost';
		
		protected $configExtension		= EXT_MOD;
		
		protected $projectPath			= 'cfg';
		protected $hostCommonPath		= 'common';
		
		private $projectBaseDirectory	= null;
		private $hostBaseDirectory		= null;
		private $version				= null;
		private $host					= null;
		
		/**
		 * @return HostConfiguration
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return HostConfiguration
		**/
		public function setup(
			$projectBaseDirectory, $hostBaseDirectory, $version, $host
		)
		{
			$this->projectBaseDirectory = $projectBaseDirectory;
			$this->hostBaseDirectory = $hostBaseDirectory;
			$this->version = $version;
			$this->host = $host;
			
			return $this;
		}
		
		public function includeConfig($name)
		{
			include $this->path().$name.$this->configExtension;
		}
		
		public function includeCommonConfig($name)
		{
			include $this->commonPath().$name.$this->configExtension;
		}
		
		public function includeProjectConfig($name)
		{
			include $this->projectPath().$name.$this->configExtension;
		}
		
		public function includeProjectCommonConfig($name)
		{
			include $this->projectCommonPath().$name.$this->configExtension;
		}
		
		protected function path()
		{
			return $this->hostBaseDirectory.$this->hostPath();
		}
		
		protected function commonPath()
		{
			return $this->hostBaseDirectory.$this->hostCommonPath();
		}
		
		protected function projectPath()
		{
			return
				$this->projectBaseDirectory
				.$this->projectPath.DIRECTORY_SEPARATOR.$this->hostPath();
		}
		
		protected function projectCommonPath()
		{
			return
				$this->projectBaseDirectory
				.$this->projectPath.DIRECTORY_SEPARATOR.$this->hostCommonPath();
		}
		
		protected function hostPath()
		{
			return
				$this->version.DIRECTORY_SEPARATOR
				.$this->host.DIRECTORY_SEPARATOR;
		}
		
		protected function hostCommonPath()
		{
			return
				$this->version.DIRECTORY_SEPARATOR
				.$this->hostCommonPath.DIRECTORY_SEPARATOR;
		}
	}
?>
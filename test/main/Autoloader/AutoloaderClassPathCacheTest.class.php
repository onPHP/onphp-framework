<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey S. Denisov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class AutoloaderClassPathCacheTest extends TestCase
	{
		public function testOneCyclic()
		{
			//setup
			$service = $this->spawnService();
			
			//simple autoload, will cache data to file
			$service->autoload('Form');
			
			//second autoload call must not require to reload cache
			$service->setNamespaceResolver($this->spawnResolver(array('getClassPathListCount' => 0)));
			$service->autoload('Sub\Form');
			
			//but if we ask force recache it will reload
			$service->setNamespaceResolver($this->spawnResolver());
			$service->autoload('Sub\Form', true);
		}
		
		public function testWithBaseException()
		{
			$service = $this->spawnService();
			$counter = 0;
			$callback = function() use (&$counter) {
				switch ($counter++) {
					case 0: return null;
					case 1: throw new BaseException('include exception');
					case 2: return null;
					default: Assert::isUnreachable($counter - 1);
				}
			};
			
			$service->expects($this->exactly(3))->
				method('includeFile')->
				will($this->returnCallback($callback));
			
			//autoload without error, it's allow us to cache data
			$service->autoload('Form');
			
			define('DEBUG', true);
			//second autoload and here we throw error
			$service->setNamespaceResolver($this->spawnResolver(array(
				'getPathsCount' => 2,
				'getClassPathListCount' => 1,
			)));
			$service->autoload('Form');
		}
		
		public function testRecacheOnChangedPath()
		{
			//setup
			$service = $this->spawnService();
			
			//simple autoload, will cache data to file
			$service->autoload('Form');
			
			//chang path list and expect recache
			$service->setNamespaceResolver($this->spawnResolver(array(
				'getPaths' => array('' => array('path1'))
			)));
			$service->autoload('Form');
		}
		
		/**
		 * @return AutoloaderClassPathCache
		 */
		private function spawnService(array $options = array())
		{
			$service = $this->getMockBuilder('AutoloaderClassPathCache')->
				setMethods(array('includeFile', 'register', 'unregister'))->
				getMock();
			
			/* @var $service AutoloaderClassPathCache */
			$service->
				setNamespaceResolver($this->spawnResolver($options))->
				setClassCachePath($this->spawnCacheDir());
			
			return $service;
		}
		
		/**
		 * @return NamespaceResolver
		 */
		private function spawnResolver(array $options = array())
		{
			$options += array(
				'getPaths' => array('' => array('path1', 'path2')),
				'getPathsCount' => 1,
				
				'getClassExtension' => EXT_CLASS,
				
				'getClassPathList' => array(
					0 => 'path1/',
					'\Form' => 0,
					1 => 'path1/path2/',
					'\Sub\Form' => 1,
				),
				'getClassPathListCount' => 1,
			);
			$mock = $this->getMock('NamespaceResolver');
			
			$mock->expects($this->any())->
				method('getClassExtension')->
				will($this->returnValue($options['getClassExtension']));
			
			$mock->expects($this->exactly($options['getPathsCount']))->
				method('getPaths')->
				will($this->returnValue($options['getPaths']));
			
			$mock->expects($this->exactly($options['getClassPathListCount']))->
				method('getClassPathList')->
				will($this->returnValue($options['getClassPathList']));
			
			return $mock;
		}
		
		private function spawnCacheDir()
		{
			$cachePath = ONPHP_CLASS_CACHE.'testCache/';
			if (file_exists($cachePath)) {
				if (is_file($cachePath))
					unlink($cachePath);
				elseif (is_dir($cachePath)) {
					FileUtils::removeDirectory($cachePath, true);
				}
			}
			mkdir($cachePath, 0777, true);
			return $cachePath;
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Konstantin V. Arkhipov                     *
 *                      2012 by Alexey S. Denisov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	class NamespaceResolverOnPHP implements NamespaceResolver
	{
		private $paths = array();
		private $classExtension = EXT_CLASS;
		
		/**
		 * @return NamespaceResolverOnPHP
		 */
		public static function create()
		{
			return new static;
		}
		
		/**
		 * @param string $path
		 * @return NamespaceResolverOnPHP
		 */
		public function addPath($path, $namespace = null)
		{
			$namespace = is_null($namespace) ? '' : trim($namespace, '\\');
			if (!isset($this->paths[$namespace])) {
				$this->paths[$namespace] = array();
			}
			
			$this->paths[$namespace][] = rtrim($path, self::DS).self::DS;
			
			return $this;
		}
		
		/**
		 * @param string $path
		 * @return NamespaceResolverOnPHP
		 */
		public function addPaths(array $pathList, $namespace = null)
		{
			foreach ($pathList as $path)
				$this->addPath($path, $namespace);
			
			return $this;
		}
		
		public function getPaths()
		{
			return $this->paths;
		}
		
		/**
		 * @param string $classExtension
		 * @return NamespaceResolverOnPHP
		 */
		public function setClassExtension($classExtension)
		{
			$this->classExtension = $classExtension;
			return $this;
		}
		
		/**
		 * @return string
		 */
		public function getClassExtension()
		{
			return $this->classExtension;
		}
		
		/**
		 * Return path to className or null if path not found
		 * 
		 * @param string $className
		 * @return string
		 */
		public function getClassPath($className)
		{
			$className = ltrim($className, '\\');
			
			foreach ($this->paths as $namespace => $namespacePaths) {
				if ($path = $this->searchClass($className, $namespace, $namespacePaths))
					return $path;
			}
		}
		
		/**
		 * Return special array numeric keys contains directories paths
		 * and other keys (className keys) contains keys of directories
		 * 
		 * @return array
		 */
		public function getClassPathList()
		{
			$dirScaner = $this->getDirScaner()
				->setClassExtension($this->getClassExtension());
			
			foreach ($this->paths as $namespace => $namespacePaths) {
				foreach ($namespacePaths as $directory) {
					$dirScaner->scan($directory, $namespace);
				}
			}
			return $dirScaner->getList();
		}
		
		protected function searchClass($className, $namespace, $paths)
		{
			$classParts = explode('\\', $className);
			$onlyClassName = array_pop($classParts);
			
			$requiredNamespace = implode('\\', $classParts);
			if ($requiredNamespace == $namespace) {
				foreach ($paths as $directory) {
					if ($paths = glob($directory.$onlyClassName.$this->classExtension, GLOB_NOSORT)) {
						return $paths[0];
					}
				}
			}
		}
		
		/**
		 * @return NamespaceDirScaner
		 */
		protected function getDirScaner() {
			return new NamespaceDirScanerOnPHP();
		}
	}
?>
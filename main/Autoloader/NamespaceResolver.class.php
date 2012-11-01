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
	
	namespace Onphp;

	interface NamespaceResolver
	{
		const DS = DIRECTORY_SEPARATOR;
		
		/**
		 * @param string $path
		 * @return \Onphp\NamespaceResolverOnPHP
		 */
		public function addPath($path, $namespace = null);
		
		/**
		 * @param array $path
		 * @return \Onphp\NamespaceResolverOnPHP
		 */
		public function addPaths(array $pathList, $namespace = null);
		
		/**
		 * @return array
		 */
		public function getPaths();
		
		/**
		 * @param string $classExtension
		 * @return \Onphp\NamespaceResolverOnPHP
		 */
		public function setClassExtension($classExtension);
		
		/**
		 * @return string
		 */
		public function getClassExtension();
		
		/**
		 * Return path to className or null if path not found
		 * 
		 * @param string $className
		 * @return string
		 */
		public function getClassPath($className);
		
		/**
		 * Return special array numeric keys contains directories paths
		 * and other keys (className keys) contains keys of directories
		 * @return array
		 */
		public function getClassPathList();
	}
?>
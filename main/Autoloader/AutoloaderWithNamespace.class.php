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

	interface AutoloaderWithNamespace extends Autoloader
	{
		/**
		 * @param \Onphp\NamespaceResolver $\Onphp\NamespaceResolver
		 * @return \Onphp\Autoloader
		 */
		public function setNamespaceResolver(NamespaceResolver $namespaceResolver);
		
		/**
		 * @return \Onphp\Autoloader
		 */
		public function getNamespaceResolver();
		
		/**
		 * @param string $path
		 * @return \Onphp\Autoloader
		 */
		public function addPath($path, $namespace = null);
		
		/**
		 * @param array $pathes
		 * @return \Onphp\Autoloader
		 */
		public function addPaths(array $paths, $namespace = null);
	}
?>
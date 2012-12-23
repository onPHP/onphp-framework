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
	
	class AutoloaderNoCache implements AutoloaderWithNamespace
	{
		/**
		 * @var NamespaceResolver
		 */
		private $namespaceResolver = null;
		
		/**
		 * @param NamespaceResolver $namespaceResolver
		 * @return AutoloaderClassPathCache
		 */
		public function setNamespaceResolver(NamespaceResolver $namespaceResolver)
		{
			$this->namespaceResolver = $namespaceResolver;
			return $this;
		}
		
		/**
		 * @return NamespaceResolver
		 */
		public function getNamespaceResolver()
		{
			return $this->namespaceResolver;
		}
		
		/**
		 * @param string $path
		 * @return AutoloaderNoCache
		 */
		public function addPath($path, $namespace = null)
		{
			$this->namespaceResolver->addPath($path, $namespace);
			
			return $this;
		}
		
		/**
		 * @param array $pathes
		 * @return AutoloaderWholeClassCache
		 */
		public function addPaths(array $paths, $namespace = null)
		{
			$this->namespaceResolver->addPaths($paths, $namespace);
			
			return $this;
		}
		
		public function autoload($className)
		{
			if (strpos($className, "\0") !== false) {
				/* are you sane? */
				return;
			}
			
			if ($path = $this->namespaceResolver->getClassPath($className)) {
				try {
					include $path;
				} catch (ClassNotFoundException $e) {
					throw $e;
				} catch (BaseException $e) {
					/* try another auto loader */
				}
			}
		}
		
		public function register()
		{
			$this->unregister();
			spl_autoload_register(array($this, 'autoload'));
			AutoloaderClassNotFound::me()->register();
		}
		
		public function unregister()
		{
			spl_autoload_unregister(array($this, 'autoload'));
		}
	}

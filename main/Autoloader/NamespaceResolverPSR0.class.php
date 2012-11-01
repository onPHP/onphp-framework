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
	 * Not for onPHP but if you want to use it in your project
	 */
	namespace Onphp;

	class NamespaceResolverPSR0 extends NamespaceResolverOnPHP
	{
		private $allowedUnderline = false;
		
		/**
		 * @return \Onphp\NamespaceResolverPSR0
		 */
		public static function create()
		{
			return new static;
		}
		
		/**
		 * @param boolean $allowedUnderline
		 * @return \Onphp\NamespaceResolverPSR0
		 */
		public function setAllowedUnderline($allowedUnderline) {
			$this->allowedUnderline = ($allowedUnderline === true);
			return $this;
		}
		
		protected function searchClass($className, $namespace, $paths)
		{
			$splitPattern = $this->allowedUnderline ? '~(\\\\+|_+)~' : '~(\\\\+)~';
			$classParts = preg_split($splitPattern, $className);
			$onlyClassName = array_pop($classParts);
			
			$requiredNamespace = implode('\\', $classParts);
			if (
				empty($namespace)
				|| $requiredNamespace == $namespace
				|| mb_strpos($requiredNamespace, $namespace) == 0
			) {
				$subPath = preg_replace('~\\\\~', self::DS, trim(
					mb_substr($requiredNamespace, mb_strlen($namespace)),
					'\\'
				));
				
				$checkPath = ($subPath ? ($subPath . self::DS) : '').$onlyClassName
					.$this->getClassExtension();
				
				foreach ($paths as $directory) {
					if ($paths = glob($directory.$checkPath)) {
						return $paths[0];
					}
				}
			}
		}
		
		/**
		 * @return \Onphp\NamespaceDirScaner
		 */
		protected function getDirScaner() {
			$dirScaner = new NamespaceDirScanerPSR0();
			return $dirScaner->setAllowedUnderline($this->allowedUnderline);
		}
	}
?>
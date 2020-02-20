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
	 * NamespaceDirScanerPSR0 class to scan directories and save which class where
	 */
	class NamespaceDirScanerPSR0 extends NamespaceDirScaner
	{
		private $allowedUnderline = false;
		
		/**
		 * @param boolean $allowedUnderline
		 * @return NamespaceResolverPSR0
		 */
		public function setAllowedUnderline($allowedUnderline) {
			$this->allowedUnderline = ($allowedUnderline === true);
			return $this;
		}
		
		public function scan($directory, $namespace = '')
		{
			$this->subScanDir($namespace, array(), $directory);
		}
		
		private function subScanDir($baseNs, array $nsList, $dir)
		{
			$this->scanCurrentDir($baseNs, $nsList, $dir);
			
			if ($paths = glob($dir.'*', GLOB_ONLYDIR)) {
				foreach ($paths as $subDir) {
					$subNs = basename($subDir);
					if (
						(mb_strpos($subNs, '.') !== false)
						|| (mb_strpos($subNs, '_') !== false)
					) {
						continue;
					}
					
					$this->subScanDir(
						$baseNs,
						array_merge($nsList, array($subNs)),
						$subDir.DIRECTORY_SEPARATOR
					);
				}
			}
		}
		
		private function scanCurrentDir($baseNs, array $nsList, $dir)
		{
			$this->list[$this->dirCount] = $dir;

			if ($paths = glob($dir.'*'.$this->classExtension)) {
				foreach ($paths as $path) {
					$classNsList = array_merge(
						$nsList,
						array(basename($path, $this->classExtension))
					);
					
					$classNs = implode('\\', $classNsList);
					$fullClassName = ($baseNs ? ('\\' . $baseNs) : '');
					$fullClassName .= ($classNs ? ('\\' . $classNs) : '');
					if (!isset($this->list[$fullClassName])) {
						$this->list[$fullClassName] = $this->dirCount;
					}
					
					if ($this->allowedUnderline) {
						$classNs = implode('_', $classNsList);
						$fullClassName = ($baseNs ? ('\\' . $baseNs) : '');
						$fullClassName .= ($classNs ? ('\\' . $classNs) : '');
						if (!isset($this->list[$fullClassName])) {
							$this->list[$fullClassName] = $this->dirCount;
						}
					}
				}
			}

			++$this->dirCount;
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PackageManager extends Singleton implements Instantiatable
	{
		const PACKAGE_CONFIG	= 'packageConfig.inc.php';

		private $packages	= array();
		private $imported	= array();

		/**
		 * @return PackageManager
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		/**
		 * @return PackageManager
		**/
		public function addPackage(
			$qualifiedName, PackageConfiguration $configuration
		)
		{
			if (isset($this->packages[$qualifiedName]))
				throw
					new WrongArgumentException(
						"package with name == {{$qualifiedName}} already exists"
					);

			$this->packages[$qualifiedName] = $configuration;

			return $this;
		}

		/**
		 * @return PackageManager
		**/
		public function import($qualifiedName)
		{
			$parts = split('.', $qualifiedName);

			$package = $classParts = null;

			$searchName = null;

			while (!empty($parts)) {
				$searchName .= array_shift($parts);

				if (isset($this->packages[$searchName])) {
					$package = $this->packages[$searchName];

					if (!isset($this->imported[$searchName])) {
						require
							$package->getBaseDirectory()
							.self::PACKAGE_CONFIG;

						$package->setAutoincludeClassPaths();

						$this->imported[$searchName] = true;
					}

					$classParts = $parts;
				}

				$searchName .= '.';
			}

			if (!isset($package))
				throw new WrongArgumentException(
					"package {{$package}} not found"
				);

			if (!empty($classParts))
				$package->importOneClass(join('.', $classParts));

			return $this;
		}
	}
?>
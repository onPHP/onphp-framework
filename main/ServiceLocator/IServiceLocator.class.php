<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	interface IServiceLocator
	{
		/**
		 * @param string $className
		 * @return object
		 */
		public function spawn($className);

		/**
		 * @param string $name
		 * @param any $service
		 * @return ServiceLocator
		 */
		public function set($name, $service);

		/**
		 * @param string $name
		 * @return any
		 */
		public function get($name);

		/**
		 * @param string $name
		 * @return ServiceLocator
		 */
		public function drop($name);

		/**
		 * @param string $name
		 * @return boolean
		 */
		public function has($name);

		/**
		 * @return array
		 */
		public function getList();
	}
?>
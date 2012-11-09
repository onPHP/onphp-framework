<?php

/* * *************************************************************************
 *   Copyright (C) 2012 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 * ************************************************************************* */

	namespace Onphp\NsConverter\AddUtils;

	use \Onphp\StaticFactory;

	

	class CMDUtils extends StaticFactory {

		/**
		 * Возвращает ассоциативный массив стартовых настроек для скрипта
		 * Настройки должны быть вида --param1=value1
		 * @return array
		 */
		public static function getOptionsList()
		{
			$uArguments = $_SERVER['argv'];
			array_shift($uArguments);
			$arguments = array();
			foreach ($uArguments as $argument) {
				if ($position = mb_strpos($argument, '=')) {
					$arguments[mb_substr($argument, 0, $position)] = mb_substr($argument, $position + 1);
				} else {
					$arguments[$argument] = true;
				}
			}

			return $arguments;
		}
	}
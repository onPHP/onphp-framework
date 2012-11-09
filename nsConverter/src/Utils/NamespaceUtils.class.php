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

namespace Onphp\NsConverter;

class NamespaceUtils
{
	public static function fixNamespace($namespace)
	{
		$namespace = trim($namespace, '\\');
		return '\\'.($namespace ? ($namespace . '\\') : '');
	}
	
	/**
	 * @param string $fullName
	 * @return array(namespace, className)
	 */
	public static function explodeFullName($fullName)
	{
		$parts = explode('\\', $fullName);
		$className = array_pop($parts);
		return [
			NamespaceUtils::fixNamespace(implode('\\', $parts)),
			$className
		];
	}
}

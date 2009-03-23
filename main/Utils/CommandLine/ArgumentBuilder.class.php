<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class ArgumentBuilder extends StaticFactory
	{
		/**
		 * @return Argument
		**/
		public static function required($name)
		{
			return
				self::createArgument($name)->
				setValueType(ArgumentValueType::required());
		}
		
		
		/**
		 * @return Argument
		**/
		public static function optional($name)
		{
			return
				self::createArgument($name)->
				setValueType(ArgumentValueType::optional());
		}
		
		
		/**
		 * @return Argument
		**/
		public static function noValue($name)
		{
			return
				self::createArgument($name)->
				setValueType(ArgumentValueType::noValue());
		}
		
		/**
		 * @return Argument
		**/
		private static function createArgument($name)
		{
			if (strlen($name) == 1)
				$type = ArgumentType::short();
			else
				$type = ArgumentType::long();
			
			return Argument::create($name, $type);
		}
	}
?>
<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Factory for various Primitives.
	 * 
	 * @ingroup Form
	**/
	final class Primitive extends StaticFactory
	{
		public static function spawn($primitive, $name)
		{
			return new $primitive($name);
		}
		
		public static function integer($name)
		{
			return new PrimitiveInteger($name);
		}
		
		public static function identifier($name)
		{
			return new PrimitiveIdentifier($name);
		}

		public static function enumeration($name)
		{
			return new PrimitiveEnumeration($name);
		}
		
		public static function date($name)
		{
			return new PrimitiveDate($name);
		}
		
		public static function string($name)
		{
			return new PrimitiveString($name);
		}
		
		public static function range($name)
		{
			return new PrimitiveRange($name);
		}
		
		public static function choice($name)
		{
			return new PrimitiveList($name);
		}
		
		public static function set($name)
		{
			return new PrimitiveArray($name);
		}

		public static function multiChoice($name)
		{
			return new PrimitiveMultiList($name);
		}
		
		public static function boolean($name)
		{
			return new PrimitiveBoolean($name);
		}
		
		public static function ternary($name)
		{
			return new PrimitiveTernary($name);
		}
		
		public static function file($name)
		{
			return new PrimitiveFile($name);
		}
		
		public static function image($name)
		{
			return new PrimitiveImage($name);
		}
		
		public static function exploded($name)
		{
			return new ExplodedPrimitive($name);
		}
	}
?>
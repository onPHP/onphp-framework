<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Factory for various Primitives.
	 * 
	 * @ingroup Form
	**/
	final class Primitive extends StaticFactory
	{
		/**
		 * @return BasePrimitive
		**/
		public static function spawn($primitive, $name)
		{
			Assert::isTrue(class_exists($primitive, true));
			
			return new $primitive($name);
		}
		
		/**
		 * @return PrimitiveAnyType
		**/
		public static function anyType($name)
		{
			return new PrimitiveAnyType($name);
		}
		
		/**
		 * @return PrimitiveInteger
		**/
		public static function integer($name)
		{
			return new PrimitiveInteger($name);
		}
		
		/**
		 * @return PrimitiveFloat
		**/
		public static function float($name)
		{
			return new PrimitiveFloat($name);
		}
		
		/**
		 * @return PrimitiveIdentifier
		**/
		public static function identifier($name)
		{
			return new PrimitiveIdentifier($name);
		}
		
		/**
		 * @return PrimitivePolymorphicIdentifier
		**/
		public static function polymorphicIdentifier($name)
		{
			return new PrimitivePolymorphicIdentifier($name);
		}
		
		/**
		 * @return PrimitiveIdentifierList
		**/
		public static function identifierlist($name)
		{
			return new PrimitiveIdentifierList($name);
		}
		
		/**
		 * @return PrimitiveEnumeration
		**/
		public static function enumeration($name)
		{
			return new PrimitiveEnumeration($name);
		}
		
		/**
		 * @return PrimitiveEnumerationList
		**/
		public static function enumerationList($name)
		{
			return new PrimitiveEnumerationList($name);
		}
		
		/**
		 * @return PrimitiveDate
		**/
		public static function date($name)
		{
			return new PrimitiveDate($name);
		}
		
		/**
		 * @return PrimitiveTimestamp
		**/
		public static function timestamp($name)
		{
			return new PrimitiveTimestamp($name);
		}
		
		/**
		 * @return PrimitiveTime
		**/
		public static function time($name)
		{
			return new PrimitiveTime($name);
		}
		
		/**
		 * @return PrimitiveString
		**/
		public static function string($name)
		{
			return new PrimitiveString($name);
		}
		
		/**
		 * @return PrimitiveBinary
		**/
		public static function binary($name)
		{
			return new PrimitiveBinary($name);
		}
		
		/**
		 * @return PrimitiveRange
		**/
		public static function range($name)
		{
			return new PrimitiveRange($name);
		}
		
		/**
		 * @return PrimitiveDateRange
		**/
		public static function dateRange($name)
		{
			return new PrimitiveDateRange($name);
		}
		
		public static function timestampRange($name)
		{
			throw new UnimplementedFeatureException();
		}
		
		/**
		 * @return PrimitiveList
		**/
		public static function choice($name)
		{
			return new PrimitiveList($name);
		}
		
		/**
		 * @return PrimitiveArray
		**/
		public static function set($name)
		{
			return new PrimitiveArray($name);
		}
		
		/**
		 * @return PrimitiveMultiList
		**/
		public static function multiChoice($name)
		{
			return new PrimitiveMultiList($name);
		}
		
		/**
		 * @return PrimitivePlainList
		**/
		public static function plainChoice($name)
		{
			return new PrimitivePlainList($name);
		}
		
		/**
		 * @return PrimitiveBoolean
		**/
		public static function boolean($name)
		{
			return new PrimitiveBoolean($name);
		}
		
		/**
		 * @return PrimitiveTernary
		**/
		public static function ternary($name)
		{
			return new PrimitiveTernary($name);
		}
		
		/**
		 * @return PrimitiveFile
		**/
		public static function file($name)
		{
			return new PrimitiveFile($name);
		}
		
		/**
		 * @return PrimitiveImage
		**/
		public static function image($name)
		{
			return new PrimitiveImage($name);
		}
		
		/**
		 * @return ExplodedPrimitive
		**/
		public static function exploded($name)
		{
			return new ExplodedPrimitive($name);
		}
		
		/**
		 * @return PrimitiveInet
		**/
		public static function inet($name)
		{
			return new PrimitiveInet($name);
		}
		
		/**
		 * @return PrimitiveForm
		**/
		public static function form($name)
		{
			return new PrimitiveForm($name);
		}
		
		/**
		 * @return PrimitiveFormsList
		**/
		public static function formsList($name)
		{
			return new PrimitiveFormsList($name);
		}
		
		/**
		 * @return PrimitiveHttpUrl
		**/
		public static function httpUrl($name)
		{
			return new PrimitiveHttpUrl($name);
		}
	}
?>
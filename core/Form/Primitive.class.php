<?php
/****************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Factory for various Primitives.
	 *
	 * @ingroup Form
	**/
	namespace Onphp;

	final class Primitive extends StaticFactory
	{
		/**
		 * @return \Onphp\BasePrimitive
		**/
		public static function spawn($primitive, $name)
		{
			Assert::classExists($primitive);
			
			return new $primitive($name);
		}
		
		/**
		 * @return \Onphp\Primitive
		**/
		public static function alias($name, BasePrimitive $prm)
		{
			return new PrimitiveAlias($name, $prm);
		}
		
		/**
		 * @return \Onphp\PrimitiveAnyType
		**/
		public static function anyType($name)
		{
			return new PrimitiveAnyType($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveInteger
		**/
		public static function integer($name)
		{
			return new PrimitiveInteger($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveFloat
		**/
		public static function float($name)
		{
			return new PrimitiveFloat($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveIdentifier
		 * @obsoleted by integerIdentifier and scalarIdentifier
		**/
		public static function identifier($name)
		{
			return new PrimitiveIdentifier($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveIntegerIdentifier
		**/
		public static function integerIdentifier($name)
		{
			return new PrimitiveIntegerIdentifier($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveScalarIdentifier
		**/
		public static function scalarIdentifier($name)
		{
			return new PrimitiveScalarIdentifier($name);
		}
		
		/**
		 * @return \Onphp\PrimitivePolymorphicIdentifier
		**/
		public static function polymorphicIdentifier($name)
		{
			return new PrimitivePolymorphicIdentifier($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveIdentifierList
		**/
		public static function identifierlist($name)
		{
			return new PrimitiveIdentifierList($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveClass
		**/
		public static function clazz($name)
		{
			return new PrimitiveClass($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveEnumeration
		**/
		public static function enumeration($name)
		{
			return new PrimitiveEnumeration($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveEnumerationByValue
		**/
		public static function enumerationByValue($name)
		{
			return new PrimitiveEnumerationByValue($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveEnumerationList
		**/
		public static function enumerationList($name)
		{
			return new PrimitiveEnumerationList($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveDate
		**/
		public static function date($name)
		{
			return new PrimitiveDate($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveTimestamp
		**/
		public static function timestamp($name)
		{
			return new PrimitiveTimestamp($name);
		}

		/**
		 * @return \Onphp\PrimitiveTimestampTZ
		**/
		public static function timestampTZ($name)
		{
			return new PrimitiveTimestampTZ($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveTime
		**/
		public static function time($name)
		{
			return new PrimitiveTime($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveString
		**/
		public static function string($name)
		{
			return new PrimitiveString($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveBinary
		**/
		public static function binary($name)
		{
			return new PrimitiveBinary($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveRange
		**/
		public static function range($name)
		{
			return new PrimitiveRange($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveDateRange
		**/
		public static function dateRange($name)
		{
			return new PrimitiveDateRange($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveTimestampRange
		**/
		public static function timestampRange($name)
		{
			return new PrimitiveTimestampRange($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveList
		**/
		public static function choice($name)
		{
			return new PrimitiveList($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveArray
		**/
		public static function set($name)
		{
			return new PrimitiveArray($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveHstore
		**/
		public static function hstore($name)
		{
			return new PrimitiveHstore($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveMultiList
		**/
		public static function multiChoice($name)
		{
			return new PrimitiveMultiList($name);
		}
		
		/**
		 * @return \Onphp\PrimitivePlainList
		**/
		public static function plainChoice($name)
		{
			return new PrimitivePlainList($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveBoolean
		**/
		public static function boolean($name)
		{
			return new PrimitiveBoolean($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveTernary
		**/
		public static function ternary($name)
		{
			return new PrimitiveTernary($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveFile
		**/
		public static function file($name)
		{
			return new PrimitiveFile($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveImage
		**/
		public static function image($name)
		{
			return new PrimitiveImage($name);
		}
		
		/**
		 * @return \Onphp\ExplodedPrimitive
		**/
		public static function exploded($name)
		{
			return new ExplodedPrimitive($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveInet
		**/
		public static function inet($name)
		{
			return new PrimitiveInet($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveForm
		**/
		public static function form($name)
		{
			return new PrimitiveForm($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveFormsList
		**/
		public static function formsList($name)
		{
			return new PrimitiveFormsList($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveNoValue
		**/
		public static function noValue($name)
		{
			return new PrimitiveNoValue($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveHttpUrl
		**/
		public static function httpUrl($name)
		{
			return new PrimitiveHttpUrl($name);
		}
		
		/**
		 * @return \Onphp\BasePrimitive
		**/
		public static function prototyped($class, $propertyName, $name = null)
		{
			Assert::isInstance($class, '\Onphp\Prototyped');
			
			$proto = is_string($class)
				? call_user_func(array($class, 'proto'))
				: $class->proto();
			
			if (!$name)
				$name = $propertyName;
			
			return $proto->getPropertyByName($propertyName)->
				makePrimitive($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveIdentifier
		**/
		public static function prototypedIdentifier($class, $name = null)
		{
			Assert::isInstance($class, '\Onphp\DAOConnected');
			
			$dao = is_string($class)
				? call_user_func(array($class, 'dao'))
				: $class->dao();
			
			return self::prototyped($class, $dao->getIdName(), $name);
		}
		
		/**
		 * @return \Onphp\PrimitiveIpAddress
		**/
		public static function ipAddress($name)
		{
			return new PrimitiveIpAddress($name);
		}
		
		/**
		 * @return \Onphp\PrimitiveIpRange
		 */
		public static function ipRange($name)
		{
			return new PrimitiveIpRange($name);
		}

		/**
		 * @return \Onphp\PrimitiveEnum
		**/
		public static function enum($name)
		{
			return new PrimitiveEnum($name);
		}

		/**
		 * @return \Onphp\PrimitiveEnumByValue
		**/
		public static function enumByValue($name)
		{
			return new PrimitiveEnumByValue($name);
		}

		/**
		 * @return \Onphp\PrimitiveEnumList
		**/
		public static function enumList($name)
		{
			return new PrimitiveEnumList($name);
		}
	}
?>
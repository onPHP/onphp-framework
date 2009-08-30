<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry E. Demidov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class DaoMoveHelper extends StaticFactory
	{
		private static $nullValue	= 0;
		private static $property	= 'position';
		
		/* void */ public static function setNullValue($nullValue)
		{
			self::$nullValue = $nullValue;
		}
		
		/* void */ public static function setProperty($property)
		{
			self::$property = $property;
		}
		
		/* void */ public static function up(
			DAOConnected $object,
			LogicalObject $exp = null
		)
		{
			$getMethod = 'get'.ucfirst(self::$property);
			
			Assert::isTrue(
				method_exists($object, $getMethod)
			);
			
			$oldPosition = $object->$getMethod();
			
			$dao = $object->dao();
			
			$query =
				ObjectQuery::create()->
				sort(self::$property)->
				desc()->
				setLimit(1);
			
			if ($exp)
				$query->addLogic($exp);
			
			$query->addLogic(
				Expression::lt(
					self::$property,
					$oldPosition
				)
			);
			
			try {
				$upperObject = $dao->get($query);
				
				DaoUtils::setNullValue(self::$nullValue);
				DaoUtils::swap($upperObject, $object, self::$property);
				
			} catch (ObjectNotFoundException $e) {
				// no need to move up top object
			}
		}
		
		/* void */ public static function down(
			DAOConnected $object,
			LogicalObject $exp = null
		)
		{
			$getMethod = 'get'.ucfirst(self::$property);
			
			Assert::isTrue(
				method_exists($object, $getMethod)
			);
			
			$oldPosition = $object->$getMethod();
			
			$dao = $object->dao();
			
			$query =
				ObjectQuery::create()->
				addLogic(
					Expression::gt(
						self::$property,
						$oldPosition
					)
				)->
				sort(self::$property)->
				asc()->
				setLimit(1);
			
			if ($exp)
				$query->addLogic($exp);
			
			try {
				$lowerObject = $dao->get($query);
				
				DaoUtils::setNullValue(self::$nullValue);
				DaoUtils::swap($lowerObject, $object, self::$property);
				
			} catch (ObjectNotFoundException $e) {
				// no need to move down bottom object
			}
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2007 by Nickolay G. Korolyov                            *
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
	final class DaoUtils extends StaticFactory
	{
		private static $nullValue	= 0;
		
		/* void */ public static function swap(
			DAOConnected $first,
			DAOConnected $second,
			$property = 'position'
		)
		{
			Assert::isTrue(
				get_class($first) === get_class($second)
			);
			
			$setMethod = 'set'.ucfirst($property);
			$getMethod = 'get'.ucfirst($property);
			
			Assert::isTrue(
				method_exists($first, $setMethod)
				&& method_exists($first, $getMethod)
			);
			
			$dao = $first->dao();
			$db = DBPool::me()->getByDao($dao);

			$oldPosition = $first->$getMethod();
			$newPosition = $second->$getMethod();
			
			$db->begin();

			$e = null;
			
			try {
				$dao->save(
					$first->$setMethod(self::$nullValue)
				);

				$dao->save(
					$second->$setMethod($oldPosition)
				);
				
				$dao->save(
					$first->$setMethod($newPosition)
				);

				$db->commit();
			} catch (DatabaseException $e) {
				$db->rollback();
			}
			
			$dao->
				uncacheByIds(
					array(
						$first->getId(), $second->getId()
					)
				);
			
			if ($e)
				throw $e;
		}
		
		/* void */ public static function setNullValue($nullValue)
		{
			self::$nullValue = $nullValue;
		}

		public static function increment(
			DAOConnected &$object,
			array $fields /* fieldName => value */,
			$refreshCurrent = true,
			/*UpdateQuery*/ $query = null
		)
		{
			$objectDao = $object->dao();

			if ($query)
				$updateQuery = $query;
			else
				$updateQuery =
					OSQL::update()->setTable($objectDao->getTable())->
					where(Expression::eqId('id', $object));

			$mapping = $objectDao->getProtoClass()->getMapping();

			foreach ($mapping as $field => $column)
				if (isset($fields[$field]))
					$updateQuery->set(
						$column,
						Expression::add($column, $fields[$field])
					);

			$updateCount =
				DBPool::getByDao($objectDao)->queryCount($updateQuery);

			if ($query)
				$objectDao->uncacheLists();
			else
				$objectDao->uncacheById($object->getId());

			if ($refreshCurrent && !$query)
				$object = $objectDao->getById($object->getId());

			return $updateCount;
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2007 by Nickolay G. Korolyov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class DaoUtils extends StaticFactory
	{
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
					$first->$setMethod(0)
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
	}
?>
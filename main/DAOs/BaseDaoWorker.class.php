<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class BaseDaoWorker implements BaseDAO
	{
		const SUFFIX_LIST	= '_list_';
		const SUFFIX_INDEX	= '_lists_index';
		const SUFFIX_QUERY	= '_query_';
		const SUFFIX_RESULT	= '_result_';

		protected $dao = null;
		
		public function __construct(GenericDAO $dao)
		{
			$this->dao = $dao;
		}
		
		public function setDao(GenericDAO $dao)
		{
			$this->dao = $dao;
			
			return $this;
		}
		
		//@{
		// erasers
		public function dropById($id)
		{
			$result =
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->dao->getTable())->
					where(Expression::eq('id', $id))
				);
			
			$this->uncacheById($id);
			
			return $result;
		}
		//@}

		//@{
		// uncachers
		public function uncacheById($id)
		{
			$className = $this->dao->getObjectName();
			
			return Cache::me()->mark($className)->delete($className.'_'.$id);
		}
		//@}
		
		//@{
		// cache getters
		public function getCachedById($id)
		{
			$className = $this->dao->getObjectName();
			
			return Cache::me()->mark($className)->get($className.'_'.$id);
		}
		
		public function getCachedByQuery(Query $query)
		{
			$className = $this->dao->getObjectName();
			
			return
				Cache::me()->mark($className)->
					get($className.self::SUFFIX_QUERY.$query->getId());
		}
		//@}
	}
?>
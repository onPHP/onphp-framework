<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup DAOs
	**/
	abstract class BaseDaoWorker implements BaseDAO
	{
		const SUFFIX_LIST	= '_list_';
		const SUFFIX_INDEX	= '_lists_index_';
		const SUFFIX_QUERY	= '_query_';
		const SUFFIX_RESULT	= '_result_';

		protected $dao = null;
		
		protected $className = null;
		
		public function __construct(GenericDAO $dao)
		{
			$this->dao = $dao;
			
			$this->className = $dao->getObjectName();
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
				DBPool::getByDao($this->dao)->queryNull(
					OSQL::delete()->from($this->dao->getTable())->
					where(Expression::eq('id', $id))
				);
			
			$this->dao->uncacheById($id);
			
			return $result;
		}
		
		public function dropByIds(/* array */ $ids)
		{
			$result =
				DBPool::getByDao($this->dao)->queryNull(
					OSQL::delete()->from($this->dao->getTable())->
					where(Expression::in($this->dao->getIdName(), $ids))
				);
			
			$this->dao->uncacheByIds($ids);
			
			return $result;
		}
		//@}

		//@{
		// uncachers
		public function uncacheById($id)
		{
			return
				Cache::me()->mark($this->className)->
					delete($this->className.'_'.$id);
		}
		
		public function uncacheByQuery(SelectQuery $query)
		{
			return
				Cache::me()->mark($this->className)->
					delete($this->className.self::SUFFIX_QUERY.$query->getId());
		}
		//@}
		
		//@{
		// cache getters
		public function getCachedById($id)
		{
			return
				Cache::me()->mark($this->className)->
					get($this->className.'_'.$id);
		}
		
		public function getCachedByQuery(SelectQuery $query)
		{
			return
				Cache::me()->mark($this->className)->
					get($this->className.self::SUFFIX_QUERY.$query->getId());
		}
		//@}
	}
?>
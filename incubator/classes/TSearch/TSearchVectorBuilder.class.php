<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: TSearchVectorBuilder.class.php 203 2008-11-27 10:03:43Z ssserj $ */

	final class TSearchVectorBuilder extends QueryIdentification
	{
		/**
		 * @var TSearchData
		**/
		protected $data = null;
		
		/**
		 * @var GenericDAO
		**/
		protected $dao = null;
		
		/**
		 * @var integer
		**/
		protected $id = null;
		
		/**
		 * @return TSearchVectorBuilder
		**/
		public static function create(TSearchConfigurator $data)
		{
			return new self($data);
		}
		
		public function __construct(TSearchConfigurator $data)
		{
			$this->dao = $data->dao();
			$this->data = $data->getTSearchData();
			$this->id = $data->getId();
		}
		
		/**
		 * @return TSearchData
		**/
		public function getTSearchData()
		{
			return $this->data;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $this->toUpdateQuery()->toDialectString($dialect);
		}
		
		/**
		 * For using in command logic
		**/
		public function save()
		{
			$db = DBPool::getByDao($this->dao);
			
			if (!$db->isQueueActive()) {
				return $db->queryCount($this);
			} else {
				return $db->queryNull($this);
			}
		}
		
		/**
		 * For using in <DAO>::setQueryFields()
		 * 
		 * @return InsertOrUpdateQuery
		**/
		public function toUpdateOrInsertQuery(InsertOrUpdateQuery $query)
		{
			$this->checkData();
			$this->checkDao();
			
			return
				$query->
					set(
						$this->dao->getIndexField(),
						$this->concatAll()
					);
		}
		
		/**
		 * @return UpdateQuery
		**/
		protected function toUpdateQuery()
		{
			$this->checkData();
			$this->checkDao();
			
			if (!$this->id)
				throw new WrongStateException(
					'save object first'
				);
			
			return
				OSQL::update()->
					setTable($this->dao->getTable())->
					set(
						$this->dao->getIndexField(),
						$this->concatAll()
					)->
					where(
						Expression::eq(
							new DBField($this->dao->getIdName()),
							new DBValue($this->id)
						)
					);
		}
		
		protected function checkData()
		{
			if (!$this->data)
				throw new WrongStateException(
					'define data first'
				);
				
			return $this;
		}
		
		protected function checkDao()
		{
			if (!$this->dao instanceof FullTextDAO)
				throw new WrongStateException(
					get_class($this->dao).' must be implement FullTextDAO'
				);
				
			return $this;
		}
		
		/**
		 * @return SQLFunction
		**/
		protected function toTsvector($data)
		{
			return
				SQLFunction::create(
					'to_tsvector',
					new DBValue(PostgresDialect::getTsConfiguration()),
					new DBValue($data)
				);
		}
		
		/**
		 * @return SQLFunction
		**/
		protected function setWeight($weight, $data)
		{
			return
				SQLFunction::create(
					'setweight',
					$this->toTsvector($data),
					new DBValue($weight)
				);
		}
		
		/**
		 * @return TSearchVectorConcatenator
		**/
		protected function concatAll()
		{
			$weights = $this->data->getWeights();
			
			if (empty($weights))
				return null;
			
			$c = new TSearchVectorConcatenator();
			
			foreach($this->data->getWeights() as $weight => $values) {
				$c->concat(
					$this->setWeight(
						$weight,
						$this->data->toStringByWeight($weight)
					)
				);
			}
			
			return $c;
		}
	}
?>
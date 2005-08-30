<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class MessageDAO extends NamedObjectDAO implements MappedDAO
	{
		// sets by constructor
		protected $fields = null;
		
		// quite plain mapping
		protected $mapping = array(
			'id'		=> 'id',
			'nickname'	=> 'nickname',
			'name'		=> 'name',
			'content'	=> 'content',
			'posted'	=> 'posted'
		);
		
		public function	__construct()
		{
			$this->fields = array_values($this->mapping);
		}
		
		public function getTable()
		{
			return 'message';
		}
		
		public function getObjectName()
		{
			return 'Message';
		}
		
		public function getMapping()
		{
			return $this->mapping;
		}
		
		public function take(Message $message)
		{
			return
				$message->getId()
					? $this->save($message)
					: $this->add($message);
		}
		
		public function save(Message $message)
		{
			return
				$this->inject(
					OSQL::update()->where(Expression::eqId('id', $message)),
					$message
				);
		}
		
		public function add(Message $message)
		{
			return 
				$this->inject(
					OSQL::insert(),
					$message->setId(
						DBFactory::getDefaultInstance()->obtainSequence(
							$this->getSequence()
						)
					)
				);
		}
		
		public function getList(ObjectQuery $oq)
		{
			return
				$this->getListByQuery(
					$oq->toSelectQuery($this)
				);
		}
		
		public function makeObject(&$array, $prefix = null)
		{
			return
				Message::create()->
				setId($array[$prefix.'id'])->
				setName($array[$prefix.'name'])->
				setNickname($array[$prefix.'nickname'])->
				setContent($array[$prefix.'content'])->
				setPosted(new Timestamp($array[$prefix.'posted']));
		}
		
		public function setQueryFields(InsertOrUpdateQuery $query, Message $message)
		{
			return
				parent::setNamedQueryFields($query, $message)->
				set('nickname', $message->getNickname())->
				set('content', $message->getContent())->
				set('posted', $message->getPosted()->toString());
		}
		
		private function inject(InsertOrUpdateQuery $query, Message $message)
		{
			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields(
					$query->setTable($this->getTable()),
					$message
				)
			);
			
			return $message;
		}
	}
?>
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

	final class MessageDAO extends MappedStorableDAO
	{
		/**
		 * plain property-to-field(s) mapping
		 * you can map class' property to a single field or to field's array()
		**/
		protected $mapping = array(
			'id'		=> null,
			'nickname'	=> null,
			'name'		=> null,
			'content'	=> null,
			'posted'	=> null
		);
		
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
			if ($query instanceof InsertQuery)
				$query->set(
					'posted',
					$message->setPosted(new Timestamp(time()))->
						getPosted()->toString()
				);
			
			return
				$query->
					set('id', $message->getId())->
					set('name', $message->getName())->
					set('nickname', $message->getNickname())->
					set('content', $message->getContent());
		}
	}
?>
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

	class main extends FormedModule
	{
		protected $messageList = array();
		
		public function init()
		{
			$this->form->
				add(
					Primitive::string('name')->
					setFilter(Filter::text())->
					required()
				)->
				add(
					Primitive::string('nickname')->
					setFilter(Filter::text())->
					required()->
					setDefault('anonymous')
				)->
				add(
					Primitive::string('content')->
					setFilter(Filter::html())->
					required()
				)->
				add(
					Primitive::boolean('add')->
					setDefault(false)
				)->
				import($_POST);
			
			return $this;
		}
		
		public function process()
		{
			$form = $this->form;
			
			if ($form->getValue('add')) {
				if (!$form->getErrors()) {
					$message =
						Message::create()->
						setName($form->getValue('name'))->
						setNickname($form->getValue('nickname'))->
						setContent($form->getValue('content'))->
						setPosted(new Timestamp(time()));
					
					try {
						$message->dao()->add($message);
	
						HeaderUtils::redirect($this);
	
						return $this;
					} catch (DatabaseException $e) {
						$form->markMissing('add');
					}
				}
			} else
				$form->dropAllErrors();
			
			$oq =
				// you can set limit/offset here too
				ObjectQuery::create()->
				sort('posted')->desc();
			
			try {
				$this->messageList = Message::dao()->getList($oq);
			} catch (ObjectNotFoundException $e) {
				// simple ignore empty list
			}
			
			return $this;
		}
	}
?>
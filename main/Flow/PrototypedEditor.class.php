<?php
/****************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */
	
	/**
	 * @ingroup Flow
	 */
	abstract class PrototypedEditor extends MethodMappedController 
	{
		const COMMAND_SUCCEEDED	= 'success';
		const COMMAND_FAILED	= 'error';
		
		protected $subject = null;
		protected $map = null;
		
		public function __construct(Prototyped $subject)
		{
			$this->subject = $subject;
			$this->map =
				MappedForm::create(
					$this->subject->proto()->makeForm()
				)->
				addSource('id', RequestType::get())->
				setDefaultType(RequestType::post());
				
			$this->form = $subject->proto()->makeForm();
			$this->
				setMethodMapping('drop', 'doDrop')->
				setMethodMapping('save', 'doSave')->
				setMethodMapping('edit', 'doEdit')->
				setMethodMapping('add', 'doAdd');
		}
		
		/**
		 * @return ModelAndView
		**/
		public function doDrop(HttpRequest $request)
		{
			$this->map->import($request);
			$form = $this->map->getForm();
			
			if ($object = $form->getValue('id')) {
				try {
					if (!$object instanceof Identifiable)
						// already deleted
						throw new ObjectNotFoundException();
					
					$object->dao()->dropById($object->getId());
					
					return ModelAndView::create()->setModel(
						Model::create()->
						set('editorResult', self::COMMAND_SUCCEEDED)
					);

				} catch (ObjectNotFoundException $e) {
					
					$form->markMissing('id');
					
					return ModelAndView::create()->setModel(
						Model::create()->
						set('editorResult', self::COMMAND_FAILED)->
						set('form', $this->form)
					);
				}
			} else {
				return ModelAndView::create()->setModel(
					Model::create()->
					set('editorResult', self::COMMAND_FAILED)->
					set('form', $form)
				);
			}
			
			Assert::isUnreachable();
		}
		
		/**
		 * @return ModelAndView
		**/
		public function doSave(HttpRequest $request)
		{
			$this->map->import($request);
			$form = $this->map->getForm();
			
			if (!$form->getErrors()) {
				
				$object = $this->saveObject($request, $form);
				
				return
					ModelAndView::create()->
					setModel(
						Model::create()->
						set('id', $object->getId())->
						set('subject', $object)->
						set('form', $form)->
						set('editorResult', self::COMMAND_SUCCEEDED)
					);
			} else {
				$model =
					Model::create()->
					set('form', $form)->
					set('editorResult', self::COMMAND_FAILED);
				
				if ($object = $form->getValue('id'))
					$model->set('subject', $object);
				
				return ModelAndView::create()->setModel($model);
			}
			
			Assert::isUnreachable();
		}
		
		public function saveObject(HttpRequest $request, Form $form) 
		{
			$object = $form->getValue('id');
			
			FormUtils::form2object($form, $object, false);
			
			return $object->dao()->save($object);
		}
		
		/**
		 * @return ModelAndView
		**/
		public function doEdit(HttpRequest $request)
		{
			$this->map->import($request);
			$form = $this->map->getForm();
			
			if ($form->getValue('id'))
				$object = $form->getValue('id');
			else 
				$object = clone $this->subject;
			
			FormUtils::object2form($object, $form);
			
			$form->dropAllErrors();
			
			return ModelAndView::create()->setModel(
				Model::create()->
				set('subject', $object)->
				set('form', $form)
			);
		}
		
		/**
		 * @return ModelAndView
		**/
		public function doAdd(HttpRequest $request)
		{
			$this->map->import($request);
			$form = $this->map->getForm();
			
			$form->markGood('id');
			
			if (!$form->getErrors()) {
				
				$object = $this->addObject($request, $form);
				
				return
					ModelAndView::create()->
					setModel(
						Model::create()->
						set('id', $object->getId())->
						set('subject', $object)->
						set('form', $form)->
						set('editorResult', self::COMMAND_SUCCEEDED)
					);
			} else {
				return
					ModelAndView::create()->
					setModel(
						Model::create()->
						set('form', $form)->
						set('subject', clone $this->subject)->
						set('editorResult', self::COMMAND_FAILED)
					);
			}
			
			Assert::isUnreachable();
		}
		
		public function addObject(HttpRequest $request, Form $form)
		{
			$object = clone $this->subject;
			FormUtils::form2object($form, $object);
			return $object->dao()->add($object);
		}
	}
?>
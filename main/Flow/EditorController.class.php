<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
	
	/**
	 * @ingroup Flow
	**/
	abstract class EditorController implements Controller
	{
		const COMMAND_SUCCEEDED	= 'success';
		const COMMAND_FAILED	= 'error';
		
		// to be redefined in __construct
		protected $commandMap	= array();
		
		protected $defaultRequestType = null;
		
		protected $map		= null;
		protected $subject 	= null;
		
		public function __construct(Prototyped $subject)
		{
			$this->commandMap['drop'] = new DropCommand();
			$this->commandMap['save'] = new SaveCommand();
			$this->commandMap['edit'] = new EditCommand();
			$this->commandMap['add'] = new AddCommand();
			
			$this->subject = $subject;
			
			$this->map =
				MappedForm::create(
					$this->subject->proto()->makeForm()->add(
						Primitive::choice('action')->setList($this->commandMap)
					)
				)->
				addSource('id', RequestType::get())->
				addSource('action', RequestType::get())->
				setDefaultType(RequestType::post());
		}
		
		public function handleRequest(HttpRequest $request)
		{
			$this->map->import($request);
			
			$form = $this->map->getForm();
			
			if ($command = $form->getValue('action'))
				$mav = $this->commandMap[$command]->run(
					$this->subject, $form, $request
				);
			else
				$mav = ModelAndView::create();
			
			if ($mav->getView() == self::COMMAND_SUCCEEDED) {
				
				$mav->setView('redirect:'.get_class($this));
				
				$mav->getModel()->
					drop('id');
				
			} else {
				$mav->setView(get_class($this));
				
				if ($command)
					$mav->getModel()->set('action', $command);
				else
					$form->dropAllErrors();
					
				$mav->setModel(
					$mav->getModel()->set('form', $form)
				);
			}
			
			return $mav;
		}
	}
?>
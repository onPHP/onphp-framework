<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	/**
	 * @ingroup Flow
	**/
	class EditorController implements Controller 
	{
		// to be redefined in __construct
		protected $commandMap	= array();
		
		protected $defaultRequestType = null;
		
		protected $map		= null;
		protected $subject 	= null;
		
		public function __construct(Prototyped $subject)
		{
			$this->commandMap = array(
				'drop'	=> new DropCommand(),
				'save'	=> new SaveCommand(),
				'edit'	=> new EditCommand(),
				'add'	=> new AddCommand()
			);
			
			$this->subject = $subject;
			
			$this->map =
				MappedForm::create(
					$this->subject->proto()->getForm()->add(
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
			
			if ($command = $form->getChoiceValue('action'))
				$mav = $command->run($this->subject, $form, $request);
			else
				$mav = ModelAndView::create();
			
			if ($mav->getView() == 'selfRedirect')
				$mav->setView('redirect:'.get_class($this));
			else {
				$mav->setView(get_class($this));
				
				if (!$model = $mav->getModel())
					$model = Model::create();
					
				$mav->setModel(
					$model->setVar('form', $form)
				);
			}
			
			return $mav;
		}
	}
?>
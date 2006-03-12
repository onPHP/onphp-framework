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
	 * Experimental object's editing business-logic.
	 * 
	 * @ingroup Module
	**/
	abstract class EditorModule extends FormedModule
	{
		// we're editing him
		protected $subject = null;
		
		/* void */ abstract protected function importForm();

		public function init()
		{
			$this->form->
				add(
					Primitive::integer('id')
				)->
				add(Primitive::string('action'))->
				import($_GET);
			
			return $this;
		}
		
		public function process()
		{
			if (!$this->subject)
				throw new WrongStateException(
					'you should create editing subject at your init'
				);
			
			$form = $this->form;
			
			$dao = $this->subject->dao();
			
			switch ($form->getValue('action')) {
				
				case 'delete':
				
					if ($id = $form->getValue('id')) {
						$dao->dropById($id);
						return $this->selfRedirect();
					}
					
					break;
				
				case 'edit':
				
					$form->dropAllErrors();

					if ($id = $form->getValue('id')) {
						$this->subject = $dao->getById($id);
						FormUtils::getValuesFrom($this->subject, $this->form);
					} else
						$form->markMissing('id');
						
					break;
				
				case 'save':
				
					if (!$form->getErrors() && $id = $form->getValue('id')) {
						$this->subject = $dao->getById($id);
						$this->importForm();
						 
						$this->subject = $dao->save($this->subject);
						
						return $this->selfRedirect();
					}
					
					break;
				
				case 'add':

					if (!$form->getErrors()) {
						
						$this->importForm();
						
						try {
							$this->subject = $dao->add($this->subject);
						} catch (DuplicateObjectException $e) {
							$form->markWrong('id');
							return $this;
						}

						return $this->selfRedirect();
					}
					
					break;
				
				default:
				
					$form->dropAllErrors();
					$this->importForm();

					break;
			}
		}
		
		protected function selfRedirect()
		{
			if ($this->form->primitiveExists('page')) {
				$page = $this->form->getActualValue('page');
				
				if ($page > 1)
					$this->addParameters(array('page' => $page));
			}
			
			HeaderUtils::redirect($this);
			
			return $this;
		}
	}
?>
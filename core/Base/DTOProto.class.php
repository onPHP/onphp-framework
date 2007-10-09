<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class DTOProto extends Singleton
	{
		private $formMapping	= array();
		
		final public function toForm($object)
		{
			if ($this->baseProto())
				$result = $this->baseProto()->toForm($object);
			else
				$result = Form::create();
			
			return
				$this->attachPrimitives($result)->
					importMore($this->buildScope($object));
		}
		
		final public function makeForm()
		{
			if ($this->baseProto())
				$result = $this->baseProto()->makeForm();
			else
				$result = Form::create();
			
			return $this->attachPrimitives($result);
		}
		
		public function baseProto()
		{
			return null;
		}
		
		public function getFormMapping()
		{
			return $this->formMapping;
		}
		
		protected function attachPrimitives(Form $form)
		{
			return $form;
		}
		
		protected function buildScope($object)
		{
			return array();
		}
	}
?>
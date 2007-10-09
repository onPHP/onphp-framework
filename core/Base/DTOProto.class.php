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
			return
				$this->
					attachPrimitives(
						$this->baseProto()
							? $this->baseProto()->toForm($object)
							: Form::create()
					)->
					importMore(
						$this->buildScope($object)
					);
		}
		
		final public function makeForm()
		{
			return
				$this->
					attachPrimitives(
						$this->baseProto()
							? $this->baseProto()->makeForm()
							: Form::create()
					);
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
<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	abstract class DTOProto extends Singleton
	{
		private $formMapping	= array();
		
		public function makeForm()
		{
			return Form::create();
		}
		
		public function getFormMapping()
		{
			return $this->formMapping;
		}
	}
?>
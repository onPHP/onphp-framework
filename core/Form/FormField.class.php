<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Atom for using in LogicalExpression.
	 * 
	 * @see DBField
	 * 
	 * @ingroup Form
	**/
	namespace Onphp;

	final class FormField
	{
		private $primitiveName	= null;
		
		public function __construct($name)
		{
			$this->primitiveName = $name;
		}
		
		/**
		 * @return \Onphp\FormField
		**/
		public static function create($name)
		{
			return new self($name);
		}

		public function getName()
		{
			return $this->primitiveName;
		}
		
		public function toValue(Form $form)
		{
			return $form->getValue($this->primitiveName);
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\NsConverter;
	
	/**
	 * Wrapper around given childs of LogicalObject with custom logic-glue's which
	 * 
	 * @ingroup Logic
	**/
	class CallbackLogicalObjectSuccess extends \Onphp\CallbackLogicalObject
	{
		/**
		 * @static
		 * @param \Closure $callback
		 * @return \Onphp\NsConverter\CallbackLogicalObjectSuccess
		 */
		static public function create(\Closure $callback)
		{
			return new self($callback);
		}

		/**
		 * @param \Onphp\Form $form
		 * @return bool
		 */
		public function toBoolean(\Onphp\Form $form)
		{
			parent::toBoolean($form);
			return true;
		}
	}
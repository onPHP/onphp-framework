<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp;

	final class ObjectToFormSetter extends FormBuilder
	{
		/**
		 * @return \Onphp\ObjectToFormSetter
		**/
		public static function create(EntityProto $proto)
		{
			return new self($proto);
		}
		
		/**
		 * @return \Onphp\ObjectGetter
		**/
		protected function getGetter($object)
		{
			return new ObjectGetter($this->proto, $object);
		}
		
		/**
		 * @return \Onphp\FormSetter
		**/
		protected function getSetter(&$object)
		{
			return new FormHardenedSetter($this->proto, $object);
		}
	}
?>
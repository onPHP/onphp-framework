<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp;

	final class ObjectSetter extends PrototypedSetter
	{
		private $getter = null;
		
		public function set($name, $value)
		{
			$setter = 'set'.ucfirst($name);
			$dropper = 'drop'.ucfirst($name);
			
			if (
				$value === null
				&& method_exists($this->object, $dropper)
			)
				$method = $dropper;
			elseif (method_exists($this->object, $setter))
				$method = $setter;
			else
				throw new WrongArgumentException(
					"cannot find mutator for '$name' in class "
					.get_class($this->object)
				);
			
			return $this->object->$method($value);
		}
		
		/**
		 * @return \Onphp\ObjectGetter
		**/
		public function getGetter()
		{
			if (!$this->getter) {
				$this->getter = new ObjectGetter($this->proto, $this->object);
			}
			
			return $this->getter;
		}
	}
?>
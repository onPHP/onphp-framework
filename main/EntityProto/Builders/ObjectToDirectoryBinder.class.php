<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class ObjectToDirectoryBinder extends DirectoryBuilder
	{
		/**
		 * @return ObjectToFormConverter
		**/
		public static function create(EntityProto $proto)
		{
			return new self($proto);
		}
		
		/**
		 * @return ObjectGetter
		**/
		protected function getGetter($object)
		{
			return new ObjectGetter($this->proto, $object);
		}
		
		/**
		 * @return FormSetter
		**/
		protected function getSetter(&$object)
		{
			return new DirectorySetter($this->proto, $object);
		}
	}
?>
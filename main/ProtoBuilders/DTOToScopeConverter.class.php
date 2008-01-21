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
/* $Id$ */

	final class DTOToScopeConverter extends PrototypedBuilder
	{
		/**
		 * @return DTOToScopeConverter
		**/
		public static function create(DTOProto $proto)
		{
			return new self($proto);
		}
		
		protected function createEmpty()
		{
			return array();
		}
		
		protected function prepareOwn($result)
		{
			return $result;
		}
		
		/**
		 * @return DTOToScopeConverter
		**/
		protected function preserveTypeLoss($result)
		{
			// NOTE: type loss here
			return $this;
		}
		
		/**
		 * @return DTOGetter
		**/
		protected function getGetter($object)
		{
			return new DTOGetter($this->proto, $object);
		}
		
		/**
		 * @return ScopeSetter
		**/
		protected function getSetter(&$object)
		{
			return new ScopeSetter($this->proto, $object);
		}
	}
?>
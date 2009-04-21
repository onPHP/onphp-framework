<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlOrderNode extends OqlMappableObjectNode
	{
		/**
		 * @return OqlOrderNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlOrderNode
		**/
		public function setObject($object)
		{
			Assert::isTrue(
				$object instanceof OrderBy
				|| $object instanceof OrderChain
			);
			
			parent::setOBject($object);
			
			return $this;
		}
		
		/**
		 * @return MappableObject
		**/
		public function toOrder()
		{
			return $this->toValue();
		}
	}
?>
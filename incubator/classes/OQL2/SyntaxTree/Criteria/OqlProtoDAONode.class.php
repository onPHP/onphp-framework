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
	final class OqlProtoDAONode extends OqlObjectNode
	{
		protected $class = 'ProtoDAO';
		
		/**
		 * @return OqlProtoDAONode
		**/
		public static function create()
		{
			return new self;
		}
		
		public function toString()
		{
			return $this->object ? $this->object->getObjectName() : null;
		}
	}
?>
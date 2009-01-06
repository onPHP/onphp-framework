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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OqlSelectPropertiesClause extends OqlSelectProjectionClause
	{
		private $distinct = false;
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public static function create()
		{
			return new self;
		}
		
		public function isDistinct()
		{
			return $this->distinct;
		}
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public function setDistinct($orly = true)
		{
			$this->distinct = ($orly === true);
			
			return $this;
		}
	}
?>
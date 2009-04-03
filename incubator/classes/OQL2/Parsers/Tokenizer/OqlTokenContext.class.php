<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
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
	final class OqlTokenContext
	{
		private $line		= null;
		private $position	= null;
		
		/**
		 * @return OqlTokenContext
		**/
		public static function create($line, $position)
		{
			return new self($line, $position);
		}
		
		public function __construct($line, $position)
		{
			$this->line = $line;
			$this->position = $position;
		}
		
		public function getLine()
		{
			return $this->line;
		}
		
		public function getPosition()
		{
			return $this->position;
		}
	}
?>
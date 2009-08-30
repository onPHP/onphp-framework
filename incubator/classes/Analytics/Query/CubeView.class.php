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

	class CubeView extends QueryObject
	{
		private $ordinateEdges	= array();
		private $pageEdges		= array();
		
		/**
		 * @return CubeView
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return EdgeView
		**/
		public function createOrdinateEdge()
		{
			$result = new EdgeView($this);
			
			$this->ordinateEdges[] = $result;
			
			return $result;
		}
		
		/**
		 * @return EdgeView
		**/
		public function createPageEdge()
		{
			$result = new EdgeView($this);
			
			$this->pageEdges[] = $result;
			
			return $result;
		}
	}
?>
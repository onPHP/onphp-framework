<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class XhtmlMpLanguage extends BaseMarkupLanguage
	{
		protected $commonName	= 'xhtmlmp';
		
		/**
		 * @return XhtmlMpLanguage
		**/
		public function create()
		{
			return new self;
		}
		
		/**
		 * @return XhtmlMpMarkupDocument
		**/
		public function parse($data)
		{
			return new UnimplementedFeatureException();
		}
		
		public function render(XhtmlMpMarkupDocument $data)
		{
			return new UnimplementedFeatureException();
		}
	}
?>